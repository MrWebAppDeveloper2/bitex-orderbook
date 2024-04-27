<?php

namespace Database\Factories;

use App\Enums\Package\PackageStatus;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'key' => Str::random(),
            'price' => $this->faker->randomFloat(),
            'service_id' => Service::factory(),
            'order_by' => 0,
            'usd' => $this->faker->randomFloat(),
            'wag' => $this->faker->randomFloat(),
            'has_item' => 0,
            'status' => PackageStatus::ENABLED->value,
        ];
    }

    /**
     * Indicate that the package is enabled
     */
    public function enable(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => PackageStatus::ENABLED->value,
            ];
        });
    }

    /**
     * Indicate that the order open
     */
    public function disable(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => PackageStatus::DISABLED->value,
            ];
        });
    }
}
