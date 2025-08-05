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
        Schema::create('telegram__users', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_id')->unique();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->float('hourly_mana_gain')->default(0);
            $table->float('monthly_mana_gain')->default(0);
            $table->float('yearly_mana_gain')->default(0);
            $table->tinyInteger('time_comparison')->default(0);
            $table->unsignedBigInteger('user_id')->unique()->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram__users');
    }
};
