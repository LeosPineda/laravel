<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'brand_name' => fake()->company(),
            'brand_logo' => null,
            'brand_image' => null,
            'qr_code_image' => null,
            'qr_mobile_number' => fake()->phoneNumber(),
            'is_active' => true,
        ];
    }
}
