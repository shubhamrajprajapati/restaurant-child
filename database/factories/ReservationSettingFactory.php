<?php

namespace Database\Factories;

use App\Models\ReservationSetting;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\OpeningHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReservationSetting>
 */
class ReservationSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReservationSetting::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            // 'restaurant_id' => Restaurant::factory(),
            // 'opening_hour_id' => OpeningHour::factory(),
            'name' => $this->faker->sentence(3),
            'active' => true,
            'ask_name' => true,
            'ask_email' => true,
            'ask_telephone' => true,
            'ask_address' => $this->faker->boolean(),
            'emails' => [$this->faker->unique()->email, $this->faker->safeEmail(), $this->faker->freeEmail()],
            'success_msg' => $this->faker->sentence(),
            'close_msg' => $this->faker->sentence(),
            'email_msg' => $this->faker->sentence(),
            'link_with_opening_hours' => $this->faker->boolean(),
            'mail_to_self' => $this->faker->boolean(),
            'mail_to_customer' => $this->faker->boolean(),
            'mail_delay' => $this->faker->numberBetween(0, 120),
            'time_interval' => $this->faker->numberBetween(15, 60),
            // 'order_column' => $this->faker->randomDigit(), // It'll be generated automatically by spatie/eloquent-sortable package
            'updated_by_user_id' => User::factory(),
            'created_by_user_id' => User::factory(),
        ];
    }
}
