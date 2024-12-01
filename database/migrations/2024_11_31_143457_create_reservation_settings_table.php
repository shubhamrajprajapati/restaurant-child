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
        Schema::create('reservation_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('restaurant_id')->nullable()->constrained('restaurants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('opening_hour_id')->nullable()->constrained('opening_hours')->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('name')->default('New Reservation Setting');
            $table->boolean('active')->default(false);

            $table->boolean('ask_name')->default(true);
            $table->boolean('ask_email')->default(true);
            $table->boolean('ask_telephone')->default(true);
            $table->boolean('ask_address')->default(false);

            $table->string('emails')->default(false);

            $table->mediumText('success_msg')->nullable();
            $table->mediumText('close_msg')->nullable();
            $table->mediumText('email_msg')->nullable();

            $table->boolean('link_with_opening_hours')->default(true);
            $table->boolean('mail_to_self')->default(true);
            $table->boolean('mail_to_customer')->default(true);

            $table->mediumInteger('mail_delay')->default(0);
            $table->mediumInteger('time_interval')->default(30);

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
        Schema::dropIfExists('reservation_setting');
    }
};
