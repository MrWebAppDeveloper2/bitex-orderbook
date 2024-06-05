<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $btc = Service::factory()->create(['name' => 'Bitcoin', 'key' => 'BTC']);

        $eth = Service::factory()->create(['name' => 'Etherium', 'key' => 'ETH']);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('123456789'),
            'balance' => "1000000000"
        ]);
        
        $user->balances()->create(['service_key' => $btc->key, 'value' => 10000]);

        $user->balances()->create(['service_key' => $eth->key, 'value' => 10000]);

        $user = User::factory()->create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => Hash::make('123456789'),
            'balance' => "1000000000"
        ]);

        $user->balances()->create(['service_key' => $btc->key, 'value' => 10000]);

        $user->balances()->create(['service_key' => $eth->key, 'value' => 10000]);
    }
}
