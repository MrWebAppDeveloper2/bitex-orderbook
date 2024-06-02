<?php

namespace Database\Factories;

use App\Models\OfferHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trade>
 */
class TradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'buy_offer_id' => OfferHistory::factory(),
            'sell_offer_id' => OfferHistory::factory(),
        ];
    }
}
