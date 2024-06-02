<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::factory()->create(['name' => 'Bitcoin', 'key' => 'BTC']);

        Service::factory()->create(['name' => 'Etherium', 'key' => 'ETH']);
    }
}
