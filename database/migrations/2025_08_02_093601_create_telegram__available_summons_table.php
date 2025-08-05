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
            ['summon' => '/tc', 'description' => 'Enable or disable time comparison to compare your mana spent with your hourly mana gain ↔️', 'type' => 'mana'],
            ['summon' => '/hmset', 'description' => 'Set your hourly mana gain ✨', 'type' => 'mana'],
            ['summon' => '/mmset', 'description' => 'Set your monthly mana gain ✨', 'type' => 'mana'],
            ['summon' => '/ymset', 'description' => 'Set your yearly mana gain ✨', 'type' => 'mana'],
            ['summon' => '/hcstatus', 'description' => 'Check the status of your hourly comparison ↔️', 'type' => 'mana'],
            ['summon' => '/hm', 'description' => 'Materialize your hourly mana gain ✨', 'type' => 'info'],
            ['summon' => '/mm', 'description' => 'Materialize your monthly mana gain ✨', 'type' => 'info'],
            ['summon' => '/ym', 'description' => 'Materialize your yearly mana gain ✨', 'type' => 'info'],
            ['summon' => '/hchm', 'description' => 'Materialize your hourly mana gain with comparison ↔️', 'type' => 'info'],
            ['summon' => '/tcmm', 'description' => 'Materialize your monthly mana gain with time comparison ↔️', 'type' => 'info'],
            ['summon' => '/tcym', 'description' => 'Materialize your yearly mana gain with time comparison ↔️', 'type' => 'info'],
            ['summon' => '/wizard', 'description' => 'Retrieve your info registered in the council 🏯', 'type' => 'info'],
            ['summon' => '/rdm', 'description' => 'Materialize your mana ✨ used today', 'type' => 'report'],
            ['summon' => '/rwm', 'description' => 'Materialize your mana ✨ used this week', 'type' => 'report'],
            ['summon' => '/rmm', 'description' => 'Materialize your mana ✨ used this month', 'type' => 'report'],
            ['summon' => '/rym', 'description' => 'Materialize your mana ✨ used this year', 'type' => 'report'],
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
