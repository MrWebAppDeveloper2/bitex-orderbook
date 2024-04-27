<?php

namespace Database\Factories;

use App\Enums\Package\PackageType;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PackageType>
 */
class PackageTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package_id' => Package::factory(),
            'type' => $this->faker->randomElement([PackageType::BUY->value, PackageType::SELL->value]),
        ];
    }
}
