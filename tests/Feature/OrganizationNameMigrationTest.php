<?php

namespace Tests\Feature;

use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrganizationNameMigrationTest extends TestCase
{
    public function test_organization_names_are_preserved_and_new_company_names_can_be_saved(): void
    {
        config([
            'database.default' => 'organization_migration_test',
            'database.connections.organization_migration_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
        ]);

        $create = require database_path('migrations/2026_10_28_000001_create_organizations_table.php');
        $migration = require database_path('migrations/2026_10_30_000002_replace_organization_names_with_name.php');
        $create->up();

        DB::table('organizations')->insert([
            'firstname' => 'StockTangent',
            'lastname' => 'Demo',
            'deleted_at' => now(),
        ]);

        $migration->up();

        $this->assertFalse(Schema::hasColumn('organizations', 'firstname'));
        $this->assertFalse(Schema::hasColumn('organizations', 'lastname'));
        $this->assertSame('StockTangent Demo', DB::table('organizations')->sole()->name);

        $organization = Organization::factory()->create(['name' => 'Example Company']);
        $this->assertSame('Example Company', $organization->fresh()->name);

        $migration->down();

        $this->assertFalse(Schema::hasColumn('organizations', 'name'));
        $this->assertSame('StockTangent Demo', DB::table('organizations')->orderBy('id')->first()->firstname);
        $this->assertSame('', DB::table('organizations')->orderBy('id')->first()->lastname);
    }
}
