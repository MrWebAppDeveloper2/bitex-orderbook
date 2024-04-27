<?php

namespace Database\Factories;

use App\Enums\Order\OrderStatus;
use App\Models\Order;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
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
            'order_id' => Order::factory(),
            'number' => rand(11111111, 999999999),
            'package_id' => Package::factory(),
            'package_price' => rand(11111111, 999999999),
            'status' => OrderStatus::OPEN->value,
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
