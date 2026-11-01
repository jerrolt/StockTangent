<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin' => 'Admin', 'member' => 'Member'] as $account => $firstname) {
            if (User::where('email', $account.'@stocktangent.com')->exists()) {
                continue;
            }

            User::factory()->create([
                'firstname' => $firstname,
                'lastname' => 'StockTangent',
                'email' => $account.'@stocktangent.com',
                'password' => Hash::make('password'),
            ]);
        }
    }
}
