<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('opening_hours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('restaurant_id')->nullable()->constrained('restaurants')->cascadeOnUpdate()->cascadeOnDelete();

            // Monday
            $table->string('monday_name')->default('Monday');
            $table->time('monday_start_time_1');
            $table->time('monday_end_time_1');
            $table->time('monday_start_time_2');
            $table->time('monday_end_time_2');
            $table->boolean('monday_holiday')->default(false);

            // Tuesday
            $table->string('tuesday_name')->default('Tuesday');
            $table->time('tuesday_start_time_1');
            $table->time('tuesday_end_time_1');
            $table->time('tuesday_start_time_2');
            $table->time('tuesday_end_time_2');
            $table->boolean('tuesday_holiday')->default(false);

            // Wednesday
            $table->string('wednesday_name')->default('Wednesday');
            $table->time('wednesday_start_time_1');
            $table->time('wednesday_end_time_1');
            $table->time('wednesday_start_time_2');
            $table->time('wednesday_end_time_2');
            $table->boolean('wednesday_holiday')->default(false);

            // Thursday
            $table->string('thursday_name')->default('Thursday');
            $table->time('thursday_start_time_1');
            $table->time('thursday_end_time_1');
            $table->time('thursday_start_time_2');
            $table->time('thursday_end_time_2');
            $table->boolean('thursday_holiday')->default(false);

            // Friday
            $table->string('friday_name')->default('Friday');
            $table->time('friday_start_time_1');
            $table->time('friday_end_time_1');
            $table->time('friday_start_time_2');
            $table->time('friday_end_time_2');
            $table->boolean('friday_holiday')->default(false);

            // Saturday
            $table->string('saturday_name')->default('Saturday');
            $table->time('saturday_start_time_1');
            $table->time('saturday_end_time_1');
            $table->time('saturday_start_time_2');
            $table->time('saturday_end_time_2');
            $table->boolean('saturday_holiday')->default(false);

            // Sunday
            $table->string('sunday_name')->default('Sunday');
            $table->time('sunday_start_time_1');
            $table->time('sunday_end_time_1');
            $table->time('sunday_start_time_2');
            $table->time('sunday_end_time_2');
            $table->boolean('sunday_holiday')->default(false);

            // Additional fields
            $table->text('message')->nullable();
            $table->text('content')->nullable();
            $table->boolean('active')->default(true);

            $table->unsignedSmallInteger('order_column')->nullable();

            $table->foreignUuid('updated_by_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('created_by_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opening_hours');
    }
};
