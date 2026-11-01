<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductQrcodeSeeder extends Seeder
{
    public function run(): void
    {
        Product::withTrashed()
            ->where(function ($query) {
                $query->whereNull('qrcode')->orWhere('qrcode', '');
            })
            ->chunkById(100, function ($products) {
                foreach ($products as $product) {
                    $product->update(['qrcode' => (string) Str::uuid()]);
                }
            });
    }
}
