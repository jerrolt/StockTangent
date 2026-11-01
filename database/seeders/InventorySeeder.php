<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use App\Models\InventoryUser;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $admin = User::where('email', 'admin@stocktangent.com')->firstOrFail();
            $member = User::where('email', 'member@stocktangent.com')->firstOrFail();
            $organization = Organization::factory()->create(['name' => 'StockTangent Demo']);

            foreach ([$admin, $member] as $user) {
                OrganizationUser::factory()->create([
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                ]);
            }

            $products = Product::factory()->count(20)->create();
            $stores = Store::factory()->count(3)->create();

            foreach ($products as $product) {
                foreach ($stores as $store) {
                    ProductStore::factory()->create([
                        'product_id' => $product->id,
                        'store_id' => $store->id,
                    ]);
                }
            }

            $inventories = collect([
                Inventory::factory()->forOrganization($organization)->create(['name' => 'Shared Supplies']),
                Inventory::factory()->create(['user_id' => $admin->id, 'name' => 'Admin Supplies']),
                Inventory::factory()->create(['user_id' => $member->id, 'name' => 'Member Supplies']),
            ]);

            foreach ($inventories as $inventory) {
                $owner = $inventory->user_id === $member->id ? $member : $admin;

                foreach ([$admin, $member] as $user) {
                    InventoryUser::factory()->create([
                        'inventory_id' => $inventory->id,
                        'user_id' => $user->id,
                        'permission' => $user->is($owner) ? 'admin' : 'editor',
                    ]);
                }

                $purchase = InventoryTransaction::factory()->create([
                    'inventory_id' => $inventory->id,
                    'user_id' => $owner->id,
                    'type' => 'purchase',
                    'notes' => 'Initial stock purchase.',
                ]);
                $usage = InventoryTransaction::factory()->create([
                    'inventory_id' => $inventory->id,
                    'user_id' => $member->id,
                    'type' => 'usage',
                    'notes' => 'Sample supply usage.',
                ]);

                foreach ($products as $product) {
                    $purchased = fake()->numberBetween(10, 50);
                    $used = fake()->numberBetween(1, $purchased);
                    $store = $stores->random();
                    $price = $product->storePrices()->where('store_id', $store->id)->firstOrFail();
                    $item = InventoryItem::factory()->create([
                        'inventory_id' => $inventory->id,
                        'product_id' => $product->id,
                        'quantity' => $purchased - $used,
                    ]);

                    InventoryTransactionItem::factory()->create([
                        'transaction_id' => $purchase->id,
                        'inventory_item_id' => $item->id,
                        'product_id' => $product->id,
                        'store_id' => $store->id,
                        'quantity' => $purchased,
                        'unit_price' => $price->price,
                        'notes' => 'Initial stock.',
                    ]);
                    InventoryTransactionItem::factory()->create([
                        'transaction_id' => $usage->id,
                        'inventory_item_id' => $item->id,
                        'product_id' => $product->id,
                        'quantity' => -$used,
                        'unit_price' => null,
                        'notes' => 'Removed from stock.',
                    ]);
                }
            }
        });
    }
}
