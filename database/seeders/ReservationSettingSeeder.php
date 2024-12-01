<?php

namespace Database\Seeders;

use App\Models\ReservationSetting;
use Illuminate\Database\Seeder;

class ReservationSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ReservationSetting::factory()->count(1)->create();
    }
}
