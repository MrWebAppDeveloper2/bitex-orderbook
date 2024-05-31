<?php

namespace Database\Seeders;

use App\Models\OfferHistory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfferHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OfferHistory::factory()->count(5)->create();
    }
}
