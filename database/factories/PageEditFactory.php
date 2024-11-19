<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageEdit>
 */
class PageEditFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(), // Generate a UUID
            'key' => $this->faker->randomElement([
                'header_section', 
                'about_section', 
                'center_section',
            ]),
            'value' => $this->faker->optional()->randomElement([
                [['key' => 'key1', 'value' => 'value1']],
                [['key' => 'key2', 'value' => 'value2']],
            ]),
            'comments' => $this->faker->sentence, // Random paragraph
            'status' => $this->faker->boolean,
            // 'order_column' => $this->faker->optional()->numberBetween(1, 100), // It'll be generated automatically by spatie/eloquent-sortable package
            'updated_by_user_id' => User::factory(), // Assuming you have a User model factory
            'created_by_user_id' => User::factory(),
        ];

    }
}
