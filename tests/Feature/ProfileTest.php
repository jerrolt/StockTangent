<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        // Profile tests only need users; unrelated inventory migrations require MySQL.
        config([
            'database.default' => 'profile_test',
            'database.connections.profile_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
            ],
        ]);
        (require database_path('migrations/0001_01_01_000000_create_users_table.php'))->up();
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'firstname' => 'Test',
                'lastname' => 'User',
                'email' => 'test@example.com',
                'address_line1' => '123 Main Street',
                'address_line2' => 'Apt 4',
                'city' => 'Chicago',
                'state' => 'il',
                'postal_code' => '00601-1234',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test', $user->firstname);
        $this->assertSame('User', $user->lastname);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('123 Main Street', $user->address_line1);
        $this->assertSame('Apt 4', $user->address_line2);
        $this->assertSame('Chicago', $user->city);
        $this->assertSame('IL', $user->state);
        $this->assertSame('00601-1234', $user->postal_code);
        $this->assertNull($user->email_verified_at);
    }

    public function test_profile_location_can_be_cleared(): void
    {
        $user = User::factory()->create([
            'address_line1' => '123 Main Street', 'address_line2' => 'Apt 4',
            'city' => 'Chicago', 'state' => 'IL', 'postal_code' => '60601',
        ]);
        $fields = ['address_line1', 'address_line2', 'city', 'state', 'postal_code'];

        $this->actingAs($user)->patch('/profile', array_merge([
            'firstname' => $user->firstname, 'lastname' => $user->lastname, 'email' => $user->email,
        ], array_fill_keys($fields, '')))->assertSessionHasNoErrors()->assertRedirect('/profile');

        $user->refresh();
        foreach ($fields as $field) {
            $this->assertNull($user->{$field});
        }
    }

    public function test_invalid_profile_location_is_rejected(): void
    {
        $user = User::factory()->create(['city' => 'Chicago']);

        $this->actingAs($user)->patch('/profile', [
            'firstname' => $user->firstname, 'lastname' => $user->lastname, 'email' => $user->email,
            'address_line1' => str_repeat('a', 256), 'address_line2' => str_repeat('b', 256),
            'city' => str_repeat('c', 101), 'state' => '123', 'postal_code' => str_repeat('0', 51),
        ])->assertSessionHasErrors(['address_line1', 'address_line2', 'city', 'state', 'postal_code']);

        $this->assertSame('Chicago', $user->fresh()->city);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'firstname' => 'Test',
                'lastname' => 'User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted($user);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
