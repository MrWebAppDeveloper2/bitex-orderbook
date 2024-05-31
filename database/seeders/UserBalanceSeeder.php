<?php

namespace Database\Seeders;

use App\Models\UserBalance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserBalance::factory()->count(5)->create();
    }
}
