<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // Isolate CRUD tests from the inventory migration's MySQL-only CHECK SQL.
        config([
            'database.default' => 'organization_test',
            'database.connections.organization_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_10_28_000001_create_organizations_table.php',
            '2026_10_28_000005_create_organization_user_table.php',
            '2026_10_30_000002_replace_organization_names_with_name.php',
        ] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
    }

    public function test_guests_cannot_access_organization_routes(): void
    {
        $organization = Organization::factory()->create();

        foreach (['/organizations', '/organizations/create', "/organizations/{$organization->id}", "/organizations/{$organization->id}/edit"] as $url) {
            $this->get($url)->assertRedirect('/login');
        }

        $this->post('/organizations', ['name' => 'Example'])->assertRedirect('/login');
        $this->put("/organizations/{$organization->id}", ['name' => 'Example'])->assertRedirect('/login');
        $this->delete("/organizations/{$organization->id}")->assertRedirect('/login');
        $this->patch("/organizations/{$organization->id}/restore")->assertRedirect('/login');
    }

    public function test_index_separates_active_and_deleted_member_organizations_and_paginates(): void
    {
        $user = User::factory()->create();
        Organization::factory()->count(16)->create()->each(fn ($organization) => $organization->users()->attach($user));
        Organization::factory()->create(['name' => 'Private organization']);
        Organization::factory()->create(['name' => 'Private deleted organization'])->delete();
        $deleted = Organization::factory()->create();
        $deleted->users()->attach($user);
        $deleted->delete();

        $this->actingAs($user)->get('/organizations')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Organizations/Index')
            ->has('organizations.data', 15)
            ->where('organizations.total', 16)
            ->has('deletedOrganizations.data', 1)
            ->where('deletedOrganizations.total', 1)
            ->where('deletedOrganizations.data.0.id', $deleted->id)
        );

        $this->get('/organizations?page=2')->assertInertia(fn (Assert $page) => $page
            ->has('organizations.data', 1)
            ->has('deletedOrganizations.data', 1)
        );
    }

    public function test_deleted_table_paginates_independently_and_preserves_active_page(): void
    {
        $user = User::factory()->create();
        Organization::factory()->count(16)->create()->each(fn ($organization) => $organization->users()->attach($user));
        Organization::factory()->count(16)->create()->each(function ($organization) use ($user) {
            $organization->users()->attach($user);
            $organization->delete();
        });

        $this->actingAs($user)->get('/organizations?page=2&deleted_page=2')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('organizations.data', 1)
            ->has('deletedOrganizations.data', 1)
            ->where('organizations.current_page', 2)
            ->where('deletedOrganizations.current_page', 2)
            ->where('organizations.prev_page_url', fn ($url) => str_contains($url, 'deleted_page=2'))
            ->where('deletedOrganizations.prev_page_url', fn ($url) => str_contains($url, 'page=2'))
        );
    }

    public function test_members_can_view_active_and_deleted_organization_summaries(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create([
            'name' => 'Example Organization',
            'description' => 'Office supply inventory.',
            'address_line1' => '123 Main Street',
        ]);
        $organization->users()->attach($user);
        $this->actingAs($user);

        foreach ([false, true] as $deleted) {
            if ($deleted) $organization->delete();

            $this->get("/organizations/{$organization->id}")->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Organizations/Show')
                ->where('organization.id', $organization->id)
                ->where('organization.name', 'Example Organization')
                ->where('organization.description', 'Office supply inventory.')
                ->where('organization.address_line1', '123 Main Street')
                ->where('organization.deleted_at', fn ($value) => $deleted ? $value !== null : $value === null)
            );
        }
    }

    public function test_nonmembers_cannot_view_active_or_deleted_organization_summaries(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->get("/organizations/{$organization->id}")->assertForbidden();
        $organization->delete();
        $this->get("/organizations/{$organization->id}")->assertForbidden();
        $this->get('/organizations/99999')->assertNotFound();
    }

    public function test_user_can_create_an_organization_and_becomes_a_member(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/organizations/create')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Organizations/Create'));

        $this->post('/organizations', [
            'name' => 'Example Company',
            'address_line1' => '123 Main Street',
            'city' => 'Austin',
            'state' => 'tx',
            'postal_code' => '78701',
            'description' => 'Shared supplies',
        ])->assertRedirect('/organizations')->assertSessionHas('success');

        $organization = Organization::sole();
        $this->assertSame('Example Company', $organization->name);
        $this->assertSame('TX', $organization->state);
        $this->assertTrue($organization->users()->whereKey($user)->exists());
    }

    public function test_member_can_edit_and_update_an_organization(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->users()->attach($user);

        $this->actingAs($user)->get("/organizations/{$organization->id}/edit")->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Organizations/Edit')
            ->where('organization.id', $organization->id)
        );

        $this->put("/organizations/{$organization->id}", ['name' => 'Updated Company', 'state' => null])
            ->assertRedirect('/organizations')->assertSessionHas('success');

        $this->assertSame('Updated Company', $organization->fresh()->name);
        $this->assertNull($organization->fresh()->state);
    }

    public function test_member_can_soft_delete_an_organization_and_return_to_the_list(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->users()->attach($user);

        $this->actingAs($user);
        $this->assertNotSoftDeleted($organization);

        $this->delete("/organizations/{$organization->id}")->assertRedirect('/organizations')->assertSessionHas('success');
        $this->assertSoftDeleted($organization);
        $this->get("/organizations/{$organization->id}/edit")->assertOk();
    }

    public function test_member_can_edit_deleted_organization_without_restoring_it(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['name' => 'Deleted Company']);
        $organization->users()->attach($user);
        $organization->delete();
        $deletedAt = $organization->deleted_at->toISOString();

        $this->actingAs($user)->get("/organizations/{$organization->id}/edit")->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Organizations/Edit')
            ->where('organization.id', $organization->id)
            ->where('organization.deleted_at', $deletedAt)
        );

        $this->put("/organizations/{$organization->id}", [
            'name' => 'Updated Deleted Company',
            'description' => 'Updated while deleted.',
            'deleted_at' => null,
        ])->assertRedirect('/organizations')->assertSessionHas('success');

        $this->assertSoftDeleted($organization);
        $this->assertSame($deletedAt, $organization->fresh()->deleted_at->toISOString());
        $this->assertSame('Updated Deleted Company', $organization->fresh()->name);
        $this->get('/organizations')->assertInertia(fn (Assert $page) => $page
            ->has('organizations.data', 0)
            ->where('deletedOrganizations.data.0.name', 'Updated Deleted Company')
        );
    }

    public function test_nonmembers_cannot_edit_or_update_deleted_organizations(): void
    {
        $organization = Organization::factory()->create(['name' => 'Private Deleted Company']);
        $organization->delete();

        $this->actingAs(User::factory()->create())->get("/organizations/{$organization->id}/edit")->assertForbidden();
        $this->put("/organizations/{$organization->id}", ['name' => 'Changed'])->assertForbidden();

        $this->assertSame('Private Deleted Company', $organization->fresh()->name);
        $this->assertSoftDeleted($organization);
    }

    public function test_nonmembers_cannot_edit_update_or_delete_organizations(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['name' => 'Private Company']);
        $this->actingAs($user)->get("/organizations/{$organization->id}/edit")->assertForbidden();
        $this->put("/organizations/{$organization->id}", ['name' => 'Changed'])->assertForbidden();
        $this->delete("/organizations/{$organization->id}")->assertForbidden();
        $this->assertSame('Private Company', $organization->fresh()->name);
        $this->assertNotSoftDeleted($organization);
    }

    public function test_member_can_restore_a_deleted_organization_to_the_active_table(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['name' => 'Restored Company']);
        $organization->users()->attach($user);
        $organization->delete();

        $this->actingAs($user)->patch("/organizations/{$organization->id}/restore")
            ->assertRedirect('/organizations')
            ->assertSessionHas('success', $organization->name.': Organization restored.');

        $this->assertNotSoftDeleted($organization);
        $this->assertSame('Restored Company', $organization->fresh()->name);
        $this->assertTrue($organization->users()->whereKey($user)->exists());
        $this->get('/organizations')->assertInertia(fn (Assert $page) => $page
            ->has('organizations.data', 1)
            ->where('organizations.data.0.id', $organization->id)
            ->has('deletedOrganizations.data', 0)
        );
        $this->get("/organizations/{$organization->id}/edit")->assertOk();
    }

    public function test_nonmembers_cannot_restore_deleted_organizations(): void
    {
        $organization = Organization::factory()->create();
        $organization->delete();

        $this->actingAs(User::factory()->create())->patch("/organizations/{$organization->id}/restore")->assertForbidden();
        $this->assertSoftDeleted($organization);
    }

    public function test_restore_rejects_active_and_missing_organizations(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->users()->attach($user);

        $this->actingAs($user)->patch("/organizations/{$organization->id}/restore")->assertNotFound();
        $this->patch('/organizations/99999/restore')->assertNotFound();
        $this->assertNotSoftDeleted($organization);
    }

    public function test_result_banner_offers_restore_after_delete_and_delete_after_restore(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['name' => 'Banner Company']);
        $organization->users()->attach($user);

        $this->actingAs($user)->delete("/organizations/{$organization->id}")
            ->assertRedirect('/organizations')
            ->assertSessionHas('organizationActionResult.status', 'success')
            ->assertSessionHas('organizationActionResult.nextAction', 'restore')
            ->assertSessionHas('organizationActionResult.organization.id', $organization->id);

        $this->get('/organizations')->assertInertia(fn (Assert $page) => $page
            ->where('actionResult.message', 'Banner Company: Organization deleted.')
            ->where('actionResult.organization.name', 'Banner Company')
            ->where('actionResult.nextAction', 'restore')
        );

        $this->patch("/organizations/{$organization->id}/restore")
            ->assertRedirect('/organizations')
            ->assertSessionHas('organizationActionResult.status', 'success')
            ->assertSessionHas('organizationActionResult.nextAction', 'delete');

        $this->get('/organizations')->assertInertia(fn (Assert $page) => $page
            ->where('actionResult.message', 'Banner Company: Organization restored.')
            ->where('actionResult.nextAction', 'delete')
        );
    }

    public function test_failed_delete_and_restore_return_error_results_with_retry_actions(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach (['delete', 'restore'] as $action) {
            $organization = Organization::factory()->create();
            $organization->users()->attach($user);
            if ($action === 'restore') $organization->delete();

            // Simulate a real database write failure without changing the controller.
            DB::unprepared("CREATE TRIGGER fail_organization_status BEFORE UPDATE OF deleted_at ON organizations BEGIN SELECT RAISE(ABORT, 'Simulated write failure'); END");

            $response = $action === 'delete'
                ? $this->delete("/organizations/{$organization->id}")
                : $this->patch("/organizations/{$organization->id}/restore");

            $response->assertRedirect('/organizations')
                ->assertSessionHas('error')
                ->assertSessionHas('organizationActionResult.status', 'error')
                ->assertSessionHas('organizationActionResult.nextAction', $action)
                ->assertSessionHas('organizationActionResult.organization.id', $organization->id);

            $this->get('/organizations')->assertInertia(fn (Assert $page) => $page
                ->where('actionResult.status', 'error')
                ->where('actionResult.nextAction', $action)
            );
            $this->assertSame($action === 'restore', $organization->fresh()->trashed());
            DB::unprepared('DROP TRIGGER fail_organization_status');
        }
    }

    public function test_create_and_edit_results_offer_actions_matching_organization_status(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/organizations', ['name' => 'New Company'])
            ->assertSessionHas('organizationActionResult.nextAction', 'delete');

        $organization = Organization::sole();
        $organization->delete();
        $this->put("/organizations/{$organization->id}", ['name' => 'Updated Deleted Company'])
            ->assertSessionHas('organizationActionResult.organization.name', 'Updated Deleted Company')
            ->assertSessionHas('organizationActionResult.nextAction', 'restore');
    }

    public function test_invalid_data_is_rejected_without_changing_organizations(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/organizations', [
            'name' => '',
            'city' => str_repeat('a', 101),
            'state' => ['TX'],
            'postal_code' => str_repeat('a', 51),
        ])->assertSessionHasErrors(['name', 'city', 'state', 'postal_code']);
        $this->assertDatabaseCount('organizations', 0);

        $organization = Organization::factory()->create(['name' => 'Original']);
        $organization->users()->attach($user);
        $this->put("/organizations/{$organization->id}", ['name' => str_repeat('a', 256), 'state' => 'Texas'])
            ->assertSessionHasErrors(['name', 'state']);
        $this->assertSame('Original', $organization->fresh()->name);
    }
}
