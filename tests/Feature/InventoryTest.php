<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'database.default' => 'inventory_test',
            'database.connections.inventory_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
            ],
        ]);
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_10_28_000001_create_organizations_table.php',
            '2026_10_28_000002_create_products_table.php',
            '2026_10_28_000003_create_stores_table.php',
            '2026_10_28_000005_create_organization_user_table.php',
            '2026_10_30_000001_add_qrcode_to_products_table.php',
            '2026_10_30_000002_replace_organization_names_with_name.php',
        ] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        // SQLite equivalent of the MySQL inventory migration, including its ownership constraint.
        DB::statement('CREATE TABLE inventories (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER REFERENCES users(id), organization_id INTEGER REFERENCES organizations(id), name VARCHAR(255) NOT NULL, description TEXT, created_at DATETIME, updated_at DATETIME, deleted_at DATETIME, CHECK ((user_id IS NOT NULL AND organization_id IS NULL) OR (user_id IS NULL AND organization_id IS NOT NULL)))');
        (require database_path('migrations/2026_10_28_000006_create_inventory_user_table.php'))->up();
        (require database_path('migrations/2026_10_28_000007_create_inventory_items_table.php'))->up();
        (require database_path('migrations/2026_10_28_000009_create_inventory_transactions_table.php'))->up();
        (require database_path('migrations/2026_10_28_000010_create_inventory_transaction_items_table.php'))->up();
        (require database_path('migrations/2026_11_22_000001_add_status_to_inventory_transactions_table.php'))->up();
    }

    public function test_guests_are_redirected_from_inventory_routes(): void
    {
        $inventory = Inventory::factory()->create();
        foreach (['/inventories', '/inventories/create', "/inventories/{$inventory->id}", "/inventories/{$inventory->id}/edit"] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->post('/inventories', ['name' => 'Supplies'])->assertRedirect('/login');
        $this->put("/inventories/{$inventory->id}", ['name' => 'Supplies'])->assertRedirect('/login');
        $this->delete("/inventories/{$inventory->id}")->assertRedirect('/login');
        $this->patch("/inventories/{$inventory->id}/restore")->assertRedirect('/login');
    }

    public function test_profile_inventory_lists_only_the_signed_in_users_owned_inventories(): void
    {
        $user = User::factory()->create();
        $ownedInventory = Inventory::factory()->create(['user_id' => $user->id]);
        $sharedInventory = Inventory::factory()->create();
        $sharedInventory->users()->attach($user, ['permission' => 'admin']);
        Inventory::factory()->create(['user_id' => $user->id])->delete();

        $this->get('/profile/inventory')->assertRedirect('/login');
        $this->actingAs($user)->get('/profile/inventory')->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Inventory')->where('user.id', $user->id)
            ->has('user.inventories', 1)->where('user.inventories.0.id', $ownedInventory->id));
    }

    public function test_create_supports_personal_and_member_organization_ownership(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->users()->attach($user);
        Organization::factory()->create();
        $this->actingAs($user)->get('/inventories/create')->assertInertia(fn (Assert $page) => $page
            ->component('Inventories/Create')->has('organizations', 1)->where('organizations.0.id', $organization->id));
        foreach ([null, $organization->id] as $organizationId) {
            $this->post('/inventories', ['name' => 'Supplies', 'organization_id' => $organizationId])
                ->assertRedirect('/inventories')->assertSessionHas('inventoryActionResult.nextAction', 'delete');
            $inventory = Inventory::latest('id')->firstOrFail();
            $this->assertSame($organizationId, $inventory->organization_id);
            $this->assertSame($organizationId ? null : $user->id, $inventory->user_id);
            $this->assertSame('admin', $inventory->users()->findOrFail($user->id)->pivot->permission);
        }
    }

    public function test_show_includes_only_this_inventorys_items_and_their_product_details(): void
    {
        $user = User::factory()->create();
        $inventory = Inventory::factory()->create(['user_id' => $user->id]);
        $item = InventoryItem::factory()->for($inventory)->create(['quantity' => '2.5000', 'reorder_level' => null]);
        $deletedItem = InventoryItem::factory()->for($inventory)->create();
        $deletedItem->delete();
        $deletedItem->product->delete();
        InventoryItem::factory()->create();

        $this->actingAs($user)->get(route('inventories.show', $inventory))->assertInertia(fn (Assert $page) => $page
            ->component('Inventories/Show')
            ->has('inventory.items', 2)
            ->where('inventory.items.0.product.name', $item->product->name)
            ->where('inventory.items.0.product.sku', $item->product->sku)
            ->where('inventory.items.0.product.unit_of_measure', $item->product->unit_of_measure)
            ->where('inventory.items.0.quantity', '2.5000')
            ->where('inventory.items.0.reorder_level', null)
            ->where('inventory.items.1.deleted_at', $deletedItem->deleted_at->toISOString())
            ->where('inventory.items.1.product.name', $deletedItem->product->name));
    }

    public function test_transactions_lists_only_the_selected_inventorys_transactions_newest_first(): void
    {
        $owner = User::factory()->create();
        $inventory = Inventory::factory()->for($owner, 'owner')->create();
        $newer = InventoryTransaction::factory()->for($inventory)->create(['created_at' => now()]);
        $older = InventoryTransaction::factory()->for($inventory)->create(['created_at' => now()->subDay()]);
        $inventoryItem = InventoryItem::factory()->for($inventory)->create();
        $item = InventoryTransactionItem::factory()->for($newer, 'transaction')->for($inventoryItem)->create([
            'quantity' => '2.5000',
            'unit_price' => '4.7500',
        ]);
        $product = $inventoryItem->product;
        $product->delete();
        InventoryTransaction::factory()->create();
        $url = route('inventories.transactions', $inventory);

        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->actingAs($owner)->get($url)->assertInertia(fn (Assert $page) => $page
            ->component('Inventories/Transactions')
            ->has('inventory.transactions', 2)
            ->where('inventory.transactions.0.id', $newer->id)
            ->has('inventory.transactions.0.items', 1)
            ->where('inventory.transactions.0.items.0.id', $item->id)
            ->where('inventory.transactions.0.items.0.quantity', '2.5000')
            ->where('inventory.transactions.0.items.0.unit_price', '4.7500')
            ->where('inventory.transactions.0.items.0.product.name', $product->name)
            ->where('inventory.transactions.0.items.0.product.unit_of_measure', $product->unit_of_measure)
            ->where('inventory.transactions.1.id', $older->id)
            ->has('inventory.transactions.1.items', 0));
    }

    public function test_transaction_details_follow_inventory_access_and_paginate_the_selected_transactions_items(): void
    {
        $owner = User::factory()->create();
        $inventory = Inventory::factory()->for($owner, 'owner')->create();
        $transaction = InventoryTransaction::factory()->for($inventory)->create([
            'reference_number' => null,
            'status' => 'completed',
        ]);
        $inventoryItem = InventoryItem::factory()->for($inventory)->create();
        $item = InventoryTransactionItem::factory()->for($transaction, 'transaction')->for($inventoryItem)->create([
            'quantity' => '2.5000',
            'unit_price' => null,
        ]);
        $remainingItems = InventoryTransactionItem::factory()->count(15)
            ->for($transaction, 'transaction')->for($inventoryItem)->create();
        InventoryTransactionItem::factory()->for($inventoryItem)->create();
        $product = $inventoryItem->product;
        $product->delete();
        $inventory->delete();
        $url = route('transactions.show', $transaction);

        $this->get($url)->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();

        $viewer = User::factory()->create();
        $inventory->users()->attach($viewer, ['permission' => 'viewer']);
        $this->actingAs($viewer)->get($url)->assertInertia(fn (Assert $page) => $page
            ->component('Transactions/Show')
            ->where('inventory.id', $inventory->id)
            ->where('inventory.owner.id', $owner->id)
            ->where('inventory.owner.firstname', $owner->firstname)
            ->where('inventory.owner.lastname', $owner->lastname)
            ->where('transaction.id', $transaction->id)
            ->where('transaction.status', 'completed')
            ->where('transaction.reference_number', null)
            ->missing('transaction.items')
            ->has('items.data', 15)
            ->where('items.total', 16)
            ->where('items.data.0.id', $item->id)
            ->where('items.data.0.product.name', $product->name)
            ->where('items.data.0.quantity', '2.5000')
            ->where('items.data.0.unit_price', null)
            ->where('items.data.0.store', null)
            ->where('items.next_page_url', $url.'?page=2'));

        $this->get($url.'?page=2')->assertInertia(fn (Assert $page) => $page
            ->component('Transactions/Show')
            ->where('items.current_page', 2)
            ->has('items.data', 1)
            ->where('items.data.0.id', $remainingItems->last()->id)
            ->where('items.prev_page_url', $url.'?page=1')
            ->where('items.next_page_url', null));
        $this->get($url.'?page=3')->assertRedirect($url.'?page=2');
        $this->get(route('transactions.show', $transaction->id + 100))->assertNotFound();
    }

    public function test_items_paginates_only_the_selected_inventorys_items(): void
    {
        $owner = User::factory()->create();
        $inventory = Inventory::factory()->for($owner, 'owner')->create();
        $firstPage = InventoryItem::factory()->count(15)->for($inventory)->create();
        $lastItem = InventoryItem::factory()->for($inventory)->create();
        $lastItem->delete();
        $lastItem->product->delete();
        InventoryItem::factory()->create();
        $url = route('inventories.items', $inventory);

        $this->get($url)->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->actingAs($owner)->get($url)->assertInertia(fn (Assert $page) => $page
            ->component('Inventories/Items')
            ->where('inventory.id', $inventory->id)
            ->missing('inventory.items')
            ->has('items.data', 15)
            ->where('items.data.0.id', $firstPage->first()->id)
            ->where('items.data.14.id', $firstPage->last()->id)
            ->where('items.total', 16)
            ->where('items.next_page_url', $url.'?page=2'));

        $this->get($url.'?page=2')->assertInertia(fn (Assert $page) => $page
            ->component('Inventories/Items')
            ->where('items.current_page', 2)
            ->has('items.data', 1)
            ->where('items.data.0.id', $lastItem->id)
            ->where('items.data.0.deleted_at', $lastItem->deleted_at->toISOString())
            ->where('items.data.0.product.name', $lastItem->product->name)
            ->where('items.prev_page_url', $url.'?page=1')
            ->where('items.next_page_url', null));

        $this->get($url.'?page=3')->assertRedirect($url.'?page=2');
    }

    public function test_index_filters_access_and_paginates_active_and_deleted_lists_independently(): void
    {
        $user = User::factory()->create();
        Inventory::factory()->count(16)->create(['user_id' => $user->id]);
        Inventory::factory()->count(16)->create(['user_id' => $user->id])->each->delete();
        Inventory::factory()->create();
        Inventory::factory()->create()->delete();
        $this->actingAs($user)->get('/inventories?page=2&deleted_page=2')->assertInertia(fn (Assert $page) => $page
            ->component('Inventories/Index')->has('inventories.data', 1)->has('deletedInventories.data', 1)
            ->where('inventories.total', 16)->where('deletedInventories.total', 16)
            ->where('inventories.data.0.can_update', true)->where('inventories.data.0.can_delete', true)
            ->where('inventories.prev_page_url', fn ($url) => str_contains($url, 'deleted_page=2'))
            ->where('deletedInventories.prev_page_url', fn ($url) => str_contains($url, 'page=2')));
    }

    public function test_owner_can_edit_delete_update_while_deleted_and_restore(): void
    {
        $user = User::factory()->create();
        $inventory = Inventory::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->get("/inventories/{$inventory->id}/edit")->assertInertia(fn (Assert $page) => $page->component('Inventories/Edit'));
        $this->delete("/inventories/{$inventory->id}")->assertSessionHas('inventoryActionResult.nextAction', 'restore');
        $this->assertSoftDeleted($inventory);
        $deletedAt = $inventory->fresh()->deleted_at->toISOString();
        $this->put("/inventories/{$inventory->id}", ['name' => 'Updated supplies', 'user_id' => User::factory()->create()->id, 'organization_id' => 999, 'deleted_at' => null])
            ->assertRedirect('/inventories')->assertSessionHas('success');
        $this->assertSame($user->id, $inventory->fresh()->user_id);
        $this->assertSame($deletedAt, $inventory->fresh()->deleted_at->toISOString());
        $this->get("/inventories/{$inventory->id}")->assertInertia(fn (Assert $page) => $page
            ->component('Inventories/Show')->where('inventory.name', 'Updated supplies')->where('inventory.owner.id', $user->id));
        $this->patch("/inventories/{$inventory->id}/restore")->assertSessionHas('inventoryActionResult.nextAction', 'delete');
        $this->assertNotSoftDeleted($inventory);
        $this->patch("/inventories/{$inventory->id}/restore")->assertNotFound();
    }

    public function test_membership_permissions_are_enforced(): void
    {
        $inventory = Inventory::factory()->create();
        foreach (['viewer', 'editor', 'admin'] as $permission) {
            $user = User::factory()->create();
            $inventory->users()->attach($user, ['permission' => $permission]);
            $this->actingAs($user)->get("/inventories/{$inventory->id}")->assertOk();
            $response = $this->put("/inventories/{$inventory->id}", ['name' => 'Shared supplies']);
            if ($permission === 'viewer') {
                $response->assertForbidden();
            } else {
                $response->assertRedirect('/inventories');
                if ($permission === 'editor') $response->assertSessionHas('inventoryActionResult', fn ($result) => $result['nextAction'] === null);
            }
            $response = $this->delete("/inventories/{$inventory->id}");
            if ($permission !== 'admin') {
                $response->assertForbidden();
            } else {
                $response->assertRedirect('/inventories');
                $this->patch("/inventories/{$inventory->id}/restore")->assertRedirect('/inventories');
            }
        }
    }

    public function test_organization_members_can_manage_organization_inventory(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->users()->attach($user);
        $inventory = Inventory::factory()->forOrganization($organization)->create();
        $this->actingAs($user)->get('/inventories')->assertInertia(fn (Assert $page) => $page
            ->where('inventories.data.0.id', $inventory->id)->where('inventories.data.0.organization.name', $organization->name));
        $this->put("/inventories/{$inventory->id}", ['name' => 'Team supplies'])->assertRedirect('/inventories');
        $this->delete("/inventories/{$inventory->id}")->assertRedirect('/inventories');
        $this->patch("/inventories/{$inventory->id}/restore")->assertRedirect('/inventories');
    }

    public function test_outsiders_cannot_access_or_change_active_or_deleted_inventories(): void
    {
        $inventory = Inventory::factory()->create();
        $this->actingAs(User::factory()->create());
        foreach ([false, true] as $deleted) {
            if ($deleted) $inventory->delete();
            $this->get("/inventories/{$inventory->id}")->assertForbidden();
            $this->get("/inventories/{$inventory->id}/edit")->assertForbidden();
            $this->put("/inventories/{$inventory->id}", ['name' => 'Changed'])->assertForbidden();
            $this->patch("/inventories/{$inventory->id}/restore")->assertForbidden();
        }
    }

    public function test_validation_rejects_invalid_fields_and_inaccessible_organizations(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $this->actingAs($user)->post('/inventories', ['name' => '', 'description' => str_repeat('x', 10001), 'organization_id' => $organization->id])
            ->assertSessionHasErrors(['name', 'description', 'organization_id']);
        $organization->users()->attach($user);
        $organization->delete();
        $this->post('/inventories', ['name' => 'Supplies', 'organization_id' => $organization->id])->assertSessionHasErrors('organization_id');
        $this->assertDatabaseCount('inventories', 0);
    }

    public function test_failed_status_changes_return_retry_feedback(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        foreach (['delete', 'restore'] as $action) {
            $inventory = Inventory::factory()->create(['user_id' => $user->id]);
            if ($action === 'restore') $inventory->delete();
            DB::unprepared("CREATE TRIGGER fail_inventory_status BEFORE UPDATE OF deleted_at ON inventories BEGIN SELECT RAISE(ABORT, 'Simulated write failure'); END");
            $response = $action === 'delete' ? $this->delete("/inventories/{$inventory->id}") : $this->patch("/inventories/{$inventory->id}/restore");
            $response->assertRedirect('/inventories')->assertSessionHas('inventoryActionResult.status', 'error')
                ->assertSessionHas('inventoryActionResult.nextAction', $action);
            $this->assertSame($action === 'restore', $inventory->fresh()->trashed());
            DB::unprepared('DROP TRIGGER fail_inventory_status');
        }
    }
}
