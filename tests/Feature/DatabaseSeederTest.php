<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_names_addresses_and_state_codes_are_populated(): void
    {
        $this->seed();

        $this->assertSame(
            ['admin@stocktangent.com', 'member@stocktangent.com'],
            User::orderBy('email')->pluck('email')->all(),
        );

        foreach (User::all() as $user) {
            $this->assertTrue(Hash::check('password', $user->password));
        }

        foreach (['users'] as $table) {
            $this->assertFalse(Schema::hasColumn($table, 'name'));

            foreach (DB::table($table)->get() as $record) {
                $this->assertNotEmpty($record->firstname);
                $this->assertNotEmpty($record->lastname);
            }
        }

        $this->assertFalse(Schema::hasColumn('organizations', 'firstname'));
        $this->assertFalse(Schema::hasColumn('organizations', 'lastname'));
        $this->assertSame('StockTangent Demo', DB::table('organizations')->sole()->name);

        foreach (['users' => 2, 'organizations' => 1, 'stores' => 3] as $table => $count) {
            $this->assertDatabaseCount($table, $count);

            foreach (DB::table($table)->get() as $record) {
                foreach (['address_line1', 'address_line2', 'city', 'postal_code'] as $field) {
                    $this->assertNotEmpty($record->{$field}, $table.'.'.$field);
                }

                $this->assertMatchesRegularExpression('/^[A-Z]{2}$/', $record->state);
            }
        }
    }
}
