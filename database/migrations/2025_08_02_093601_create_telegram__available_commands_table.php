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
        Schema::create('telegram__available_commands', function (Blueprint $table) {
            $table->id();
            $table->string('command')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('type', ['base', 'mana', 'reports', 'info'])->default('base');
            $table->timestamps();
        });

        DB::table('telegram__available_commands')->insert([
            ['command' => '/start', 'description' => 'Start your journey and learn the rules of the council 🏯', 'type' => 'base'],
            ['command' => '/help', 'description' => 'Materialize all available summons 🪄', 'type' => 'base'],
            ['command' => '/register', 'description' => 'Register a new wizard, be part of the council 🏯', 'type' => 'base'],
            ['command' => '/avadakedavra', 'description' => 'You have used a cursed spell 🪄 you are OUT of the council.', 'type' => 'base'],
            ['command' => '/hc', 'description' => 'Enable or disable hourly comparison of your mana gain ↔️', 'type' => 'mana'],
            ['command' => '/hmset', 'description' => 'Set your hourly mana gain ✨', 'type' => 'mana'],
            ['command' => '/mmset', 'description' => 'Set your monthly mana gain ✨', 'type' => 'mana'],
            ['command' => '/ymset', 'description' => 'Set your yearly mana gain ✨', 'type' => 'mana'],
            ['command' => '/hcstatus', 'description' => 'Check the status of your hourly comparison ↔️', 'type' => 'mana'],
            ['command' => '/hm', 'description' => 'Materialize your hourly mana gain ✨', 'type' => 'info'],
            ['command' => '/mm', 'description' => 'Materialize your monthly mana gain ✨', 'type' => 'info'],
            ['command' => '/ym', 'description' => 'Materialize your yearly mana gain ✨', 'type' => 'info'],
            ['command' => '/hchm', 'description' => 'Materialize your hourly mana gain with comparison ↔️', 'type' => 'info'],
            ['command' => '/hcmm', 'description' => 'Materialize your monthly mana gain with comparison ↔️', 'type' => 'info'],
            ['command' => '/hcym', 'description' => 'Materialize your yearly mana gain with comparison ↔️', 'type' => 'info'],
            ['command' => '/wizard', 'description' => 'Retrieve your info registered in the council 🏯', 'type' => 'info'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram__available_commands');
    }
};
