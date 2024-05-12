<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => rand(1, 10),
            'name' => $this->faker->name,
            'logo' => $this->faker->imageUrl,
            'status' => 1,
            'is_pack' => 0,
            'order_by' => 1,
            'text' => $this->faker->text
        ];
    }
}
