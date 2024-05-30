<?php

namespace Database\Factories;

use App\Enums\Offer\OfferType;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'remaining_amount' => rand(100000, 999999),
            'price' => rand(10000, 99999),
            'type' => $this->faker->randomElement([OfferType::BUY->value, OfferType::SELL->value]),
            'service_id' => Service::factory(),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate buy type for offer.
     */
    public function buy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => OfferType::BUY->value,
        ]);
    }

    /**
     * Indicate sell type for offer.
     */
    public function sell(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => OfferType::SELL->value,
        ]);
    }
}
