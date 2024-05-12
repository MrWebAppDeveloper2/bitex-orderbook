<?php

namespace Database\Factories;

use App\Enums\Order\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->date,
            'user_id' => User::factory(),
            'status' => OrderStatus::OPEN->value,
            'payment_id' => null,
            'payment_method' => null,
        ];
    }

    /**
     * Indicate that the order done
     */
    public function done(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => OrderStatus::DONE->value,
            ];
        });
    }

    /**
     * Indicate that the order open
     */
    public function open(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => OrderStatus::OPEN->value,
            ];
        });
    }
}
