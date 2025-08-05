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
        Schema::create('telegram__available_summons_similarities', function (Blueprint $table) {
            $table->id();
            $table->string('summon')->unique();
            $table->string('similarity_summon');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('similarity_summon', 'fk_ss')
                ->references('summon')
                ->on('telegram__available_summons')
                ->onDelete('cascade');
        });

        DB::table('telegram__available_summons_similarities')->insert([
            // SET comparison time
            ['summon' => '/set_time_comparison', 'similarity_summon' => '/stc'],
            ['summon' => '/set-time-comparison', 'similarity_summon' => '/stc'],
            ['summon' => '/setTimeComparison', 'similarity_summon' => '/stc'],

            // VIEW comparison time
            ['summon' => '/view_time_comparison', 'similarity_summon' => '/vtc'],
            ['summon' => '/view-time-comparison', 'similarity_summon' => '/vtc'],
            ['summon' => '/viewTimeComparison', 'similarity_summon' => '/vtc'],

            // SET hourly mana gain
            ['summon' => '/set-hourly-mana-gain', 'similarity_summon' => '/shmgg'],
            ['summon' => '/set_hourly_mana_gain', 'similarity_summon' => '/shmgg'],
            ['summon' => '/setHourlyManaGain', 'similarity_summon' => '/shmgg'],

            // VIEW hourly mana gain
            ['summon' => '/view-hourly-mana-gain', 'similarity_summon' => '/vhmg'],
            ['summon' => '/view_hourly_mana_gain', 'similarity_summon' => '/vhmg'],
            ['summon' => '/viewHourlyManaGain', 'similarity_summon' => '/vhmg'],

            // SET monthly mana gain
            ['summon' => '/set-monthly-mana-gain', 'similarity_summon' => '/smmg'],
            ['summon' => '/set_monthly_mana_gain', 'similarity_summon' => '/smmg'],
            ['summon' => '/setMonthlyManaGain', 'similarity_summon' => '/smmg'],

            // VIEW monthly mana gain
            ['summon' => '/view-monthly-mana-gain', 'similarity_summon' => '/vmmg'],
            ['summon' => '/view_monthly_mana_gain', 'similarity_summon' => '/vmmg'],
            ['summon' => '/viewMonthlyManaGain', 'similarity_summon' => '/vmmg'],

            // SET yearly mana gain
            ['summon' => '/set-yearly-mana-gain', 'similarity_summon' => '/symg'],
            ['summon' => '/set_yearly_mana_gain', 'similarity_summon' => '/symg'],
            ['summon' => '/setYearlyManaGain', 'similarity_summon' => '/symg'],

            // VIEW yearly mana gain
            ['summon' => '/view-yearly-mana-gain', 'similarity_summon' => '/vymg'],
            ['summon' => '/view_yearly_mana_gain', 'similarity_summon' => '/vymg'],
            ['summon' => '/viewYearlyManaGain', 'similarity_summon' => '/vymg'],

            // VIEW mana spent today
            ['summon' => '/view-daily-mana-spent', 'similarity_summon' => '/vdms'],
            ['summon' => '/view_daily_mana_spent', 'similarity_summon' => '/vdms'],
            ['summon' => '/viewDailyManaSpent', 'similarity_summon' => '/vdms'],

            // VIEW mana spent this week
            ['summon' => '/view-weekly-mana-spent', 'similarity_summon' => '/vwms'],
            ['summon' => '/view_weekly_mana_spent', 'similarity_summon' => '/vwms'],
            ['summon' => '/viewWeeklyManaSpent', 'similarity_summon' => '/vwms'],

            // VIEW mana spent this month
            ['summon' => '/view-monthly-mana-spent', 'similarity_summon' => '/vmms'],
            ['summon' => '/view_monthly_mana_spent', 'similarity_summon' => '/vmms'],
            ['summon' => '/viewMonthlyManaSpent', 'similarity_summon' => '/vmms'],

            // VIEW mana spent this year
            ['summon' => '/view-yearly-mana-spent', 'similarity_summon' => '/vyms'],
            ['summon' => '/view_yearly_mana_spent', 'similarity_summon' => '/vyms'],
            ['summon' => '/viewYearlyManaSpent', 'similarity_summon' => '/vyms'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram__available_summons_similarities');
    }
};
