<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('telegram__available_summons', function (Blueprint $table) {
            $table->id();
            $table->string('summon')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('type', ['base', 'mana', 'report', 'info'])->default('base');
            $table->timestamps();
        });

        DB::table('telegram__available_summons')->insert([
            ['summon' => '/rules', 'description' => 'Learn the rules of the council 🏯', 'type' => 'base'],
            ['summon' => '/help', 'description' => 'Materialize all available summons 🪄', 'type' => 'base'],
            ['summon' => '/register', 'description' => 'Register a new wizard, be part of the council 🏯', 'type' => 'base'],
            ['summon' => '/avadakedavra', 'description' => 'You have used a cursed spell 🪄 you are OUT of the council.', 'type' => 'base'],

            // MANA
            ['summon' => '/stc', 'description' => 'Enable or disable time comparison to compare your mana spent with your hourly mana gain ↔️', 'type' => 'mana'],
            ['summon' => '/shmgg', 'description' => 'Set your hourly mana gain ✨', 'type' => 'mana'],
            ['summon' => '/smmg', 'description' => 'Set your monthly mana gain ✨', 'type' => 'mana'],
            ['summon' => '/symg', 'description' => 'Set your yearly mana gain ✨', 'type' => 'mana'],

            // INFO
            ['summon' => '/vtc', 'description' => 'Check your time comparison choice ↔️', 'type' => 'info'],
            ['summon' => '/vhmg', 'description' => 'Materialize your hourly mana gain ✨', 'type' => 'info'],
            ['summon' => '/vmmg', 'description' => 'Materialize your monthly mana gain ✨', 'type' => 'info'],
            ['summon' => '/vymg', 'description' => 'Materialize your yearly mana gain ✨', 'type' => 'info'],
            ['summon' => '/vdms', 'description' => 'Materialize your mana ✨ spent today', 'type' => 'info'],
            ['summon' => '/vwms', 'description' => 'Materialize your mana ✨ spent this week', 'type' => 'info'],
            ['summon' => '/vmms', 'description' => 'Materialize your mana ✨ spent this month', 'type' => 'info'],
            ['summon' => '/vyms', 'description' => 'Materialize your mana ✨ spent this year', 'type' => 'info'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram__available_summons');
    }
};
