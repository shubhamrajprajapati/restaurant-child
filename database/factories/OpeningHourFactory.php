<?php

namespace Database\Factories;

use App\Models\OpeningHour;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OpeningHour>
 */
class OpeningHourFactory extends Factory
{
    protected $model = OpeningHour::class;

    public function definition(): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $data = [];
        foreach ($days as $day) {
            $lowerDay = strtolower($day);
            $data["{$lowerDay}_name"] = $day;
            $data["{$lowerDay}_start_time_1"] = $this->faker->time('H:i:s', '09:00:00');
            $data["{$lowerDay}_end_time_1"] = $this->faker->time('H:i:s', '12:00:00');
            $data["{$lowerDay}_start_time_2"] = $this->faker->time('H:i:s', '14:00:00');
            $data["{$lowerDay}_end_time_2"] = $this->faker->time('H:i:s', '18:00:00');
            $data["{$lowerDay}_open"] = $this->faker->boolean(10); // 10% chance it's a holiday
        }

        return array_merge($data, [
            // 'restaurant_id' => Restaurant::factory(),
            'message' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'active' => true,
            // 'order_column' => $this->faker->numberBetween(1, 100), // Will auto generate by sortable trait
            'updated_by_user_id' => User::factory(),
            'created_by_user_id' => User::factory(),
        ]);
    }
}
