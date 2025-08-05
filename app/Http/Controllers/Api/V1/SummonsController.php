<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ManaSpent;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\TelegramAvailableSummon;

class SummonsController extends Controller
{
    public static function handleSummon(Request $request, string $summon)
    {
        $summonsWithSimilarities = self::getSummonsWithSimilarities();

        switch ($summon) {
            case '/rules':
            case isset($summonsWithSimilarities['/rules']) && in_array($summon, $summonsWithSimilarities['/rules']):
                return self::handleRulesSummon($request);
            case '/help':
            case isset($summonsWithSimilarities['/help']) && in_array($summon, $summonsWithSimilarities['/help']):
                return self::handleHelpSummon($request);
            case '/register':
            case isset($summonsWithSimilarities['/register']) && in_array($summon, $summonsWithSimilarities['/register']):
                return self::handleRegisterSummon($request);
            case '/stc':
            case isset($summonsWithSimilarities['/stc']) && in_array($summon, $summonsWithSimilarities['/stc']):
                return self::handleSetTimeComparisonSummon($request);
            case '/shmg':
            case isset($summonsWithSimilarities['/shmg']) && in_array($summon, $summonsWithSimilarities['/shmg']):
                return self::handleSetManaSummon($request, '/shmg');
            case '/smmg':
            case isset($summonsWithSimilarities['/smmg']) && in_array($summon, $summonsWithSimilarities['/smmg']):
                return self::handleSetManaSummon($request, '/smmg');
            case '/symg':
            case isset($summonsWithSimilarities['/symg']) && in_array($summon, $summonsWithSimilarities['/symg']):
                return self::handleSetManaSummon($request, '/symg');
            case '/vdms':
            case isset($summonsWithSimilarities['/vdms']) && in_array($summon, $summonsWithSimilarities['/vdms']):
                return self::handleManaSpentInfoSummon($request, '/vdms');
            case '/vwms':
            case isset($summonsWithSimilarities['/vwms']) && in_array($summon, $summonsWithSimilarities['/vwms']):
                return self::handleManaSpentInfoSummon($request, '/vwms');
            case '/vmms':
            case isset($summonsWithSimilarities['/vmms']) && in_array($summon, $summonsWithSimilarities['/vmms']):
                return self::handleManaSpentInfoSummon($request, '/vmms');
            case '/vyms':
            case isset($summonsWithSimilarities['/vyms']) && in_array($summon, $summonsWithSimilarities['/vyms']):
                return self::handleManaSpentInfoSummon($request, '/vyms');
            default:
                app('telegramBot')->sendMessage(
                    'Wizard, The council do not know this summon. Please cast 🪄 /help to see all available summons.',
                    $request->input('message.chat.id'),
                    null
                );
        }
    }

    public static function getSummonsWithSimilarities(): array
    {
        // Fetch all active summons from the database
        $summonsWithSimilarities = DB::select('SELECT 
                                                    s.summon,
                                                    CASE 
                                                        WHEN COUNT(sim.summon) > 0 
                                                        THEN JSON_ARRAYAGG(sim.summon) 
                                                        ELSE JSON_ARRAY() 
                                                    END as similarities_array
                                                FROM telegram__available_summons s
                                                LEFT JOIN telegram__available_summons_similarities sim ON s.summon = sim.similarity_summon
                                                GROUP BY s.summon;'
                                            );
        
        $summonsArray = [];
        
        foreach ($summonsWithSimilarities as $row) {
            $summonsArray[$row->summon] = json_decode($row->similarities_array, true);
        }

        return $summonsArray;
    }

    protected static function handleRulesSummon(Request $request): void
    {
        $textToSend = '';

        // TODO: implement

        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null // actual message ID if needed to reply to
        );
    }

    protected static function handleRegisterSummon(Request $request): void
    {
        $textToSend = '';

        // check if the user already exists in the DB
        if (TelegramUser::where('telegram_id', $request->input('message.from.id'))->exists()) {
            $textToSend = 'You are already part of the council, Wizard 🧙🏼‍♂️.';
        } else {
            // create a new TelegramUser or update existing one
            TelegramUser::updateOrCreate(
                [
                    'telegram_id' => $request->input('message.from.id')
                ],
                [
                    'username' => $request->input('message.from.username', null),
                    'first_name' => $request->input('message.from.first_name', null),
                    'last_name' => $request->input('message.from.last_name', null),
                ]
            );

            $textToSend = 'You are now part of the council, Wizard 🧙🏼‍♂️. Use /rules to begin your journey and learn the rules of the council 🏯.';
        }


        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null
        );
    }

    protected static function handleHelpSummon(Request $request): void
    {
        $textToSend = "🪄 Available summons in the council of Wizardry 🪄\n\n";
        
        $summonsList = TelegramAvailableSummon::where('is_active', true)->get();

        $baseSummons = "";
        $manaSummons = "";
        $infoSummons = "";
        $reportSummons = "";

        if ($summonsList->isEmpty()) {
            $textToSend = "No available summons at the moment. Please check back later.";
        } else {
            foreach ($summonsList as $summon) {

                switch ($summon->type) {
                    case 'base':

                        if (empty($baseSummons)) {
                            $baseSummons .= "Base 🏯:\n";
                        }

                        $baseSummons .= "{$summon->summon} - {$summon->description}\n";

                        break;
                    case 'mana':

                        if (empty($manaSummons)) {
                            $manaSummons .= "Mana ✨:\n";
                        }

                        $manaSummons .= "{$summon->summon} - {$summon->description}\n";

                        break;
                    case 'info':

                        if (empty($infoSummons)) {
                            $infoSummons .= "Info 📜:\n";
                        }

                        $infoSummons .= "{$summon->summon} - {$summon->description}\n";

                        break;
                    case 'report':

                        if (empty($reportSummons)) {
                            $reportSummons .= "Report 📊:\n";
                        }

                        $reportSummons .= "{$summon->summon} - {$summon->description}\n";

                        break;
                    default:
                        Log::warning('Unknown summon type', ['type' => $summon->type]);
                        break;
                }

            }

        }

        $textToSend .= $baseSummons . "\n";
        $textToSend .= $manaSummons . "\n";
        $textToSend .= $infoSummons . "\n";
        $textToSend .= $reportSummons . "\n";

        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null
        );
    }

    protected static function handleSetTimeComparisonSummon(Request $request): void
    {
        $telegramUser = TelegramUser::where('telegram_id', $request->input('message.from.id'))->first();

        // if the user does not exist, send a message to register
        if (!$telegramUser) {
            $textToSend = env('TELEGRAM_BOT_REGISTER_MSG', 'Registration message not set in .env file. Please set TELEGRAM_BOT_REGISTER_MSG variable.');
        } else {
            // if the user does not have an hourly mana gain set, send a message to set it
            if (!$telegramUser->hourly_mana_gain) {
                $textToSend = "You have not set your hourly mana gain yet. You can cast 🪄 /shmg followed by the amount of your hourly mana gain ✨ to update it. (Example: /shmg 7.5)";
            } else {
                $textToSend = "Your summon was successfully casted! Your time comparison is ";
                $telegramUser->time_comparison = !$telegramUser->time_comparison;
                $telegramUser->save();
                $textToSend .= $telegramUser->time_comparison ? 'enabled ✅' : 'disabled ❌';

                // if is enabled, provide the current hourly mana gain
                if ($telegramUser->time_comparison) {
                    $textToSend .= ". Your current hourly mana gain ✨ is {$telegramUser->hourly_mana_gain}.";
                    $textToSend .= " You can cast 🪄 /shmg followed by the amount of your hourly mana gain ✨ to update it. (Example: /shmg 7.5)";
                }

            }
        }


        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null
        );
    }

    protected static function handleSetManaSummon(Request $request, string $summon): void
    {
        $telegramUser = TelegramUser::where('telegram_id', $request->input('message.from.id'))->first();

        // if the user does not exist, send a message to register
        if (!$telegramUser) {
            $textToSend = env('TELEGRAM_BOT_REGISTER_MSG', 'Registration message not set in .env file. Please set TELEGRAM_BOT_REGISTER_MSG variable.');
        } else {
            // assuming the summon is followed by the mana amount remove any non-numeric characters except for the decimal point
            $mana = preg_replace('/[^\d.]/', '', $request->input('message.text', ''));
            // replace comma with dot for decimal point
            $mana = str_replace(',', '.', $mana);
            $mana = trim($mana);
            $mana = floatval($mana);
            $time = '';

            if ($summon === '/shmg') {
                $time = "hourly";
                $property_to_update = 'hourly';
            } else if ($summon === '/smmg') {
                $time = "monthly";
                $property_to_update = 'monthly';
            } else if ($summon === '/symg') {
                $time = "yearly";
                $property_to_update = 'yearly';
            } else {
                return; // Invalid summon, do nothing
            }

            $property_to_update .= '_mana_gain';

            if ($mana <= 0 || !is_numeric($mana) || empty($mana)) {
                $textToSend = "Please provide a valid {$time} mana gain amount ✨. Example: /{$summon} 7.5";
            } else {
                $telegramUser->{$property_to_update} = $mana;
                $textToSend = "Your {$time} mana gain ✨ has been set to {$mana}.";
                $telegramUser->save();
            }
        }

        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null
        );
    }

    protected static function handleManaSpentInfoSummon(Request $request, string $summon): void
    {
        $telegramUser = TelegramUser::where('telegram_id', $request->input('message.from.id'))->first();

        // if the user does not exist, send a message to register
        if (!$telegramUser) {
            $textToSend = env('TELEGRAM_BOT_REGISTER_MSG', 'Registration message not set in .env file. Please set TELEGRAM_BOT_REGISTER_MSG variable.');
        } else {
            
            if ($summon === '/vdms') {
                $time_start = now()->startOfDay();
                $time_end = now()->endOfDay();
                $period = 'day';
            } else if ($summon === '/vwms') {
                $time_start = now()->startOfWeek();
                $time_end = now()->endOfWeek();
                $period = 'week';
            } else if ($summon === '/vmms') {
                $time_start = now()->startOfMonth();
                $time_end = now()->endOfMonth();
                $period = 'month';
            } else if ($summon === '/vyms') {
                $time_start = now()->startOfYear();
                $time_end = now()->endOfYear();
                $period = 'year';
            }

            $transactions = ManaSpent::where('telegram_id', $telegramUser->telegram_id)
                ->whereBetween('updated_at', [$time_start, $time_end])
                ->get();
            
            if ($transactions->isEmpty()) {
                $textToSend = "You have not used any amount of mana ✨ during this period.";
            } else {
                $totalAmount = $transactions->sum('amount');
                $textToSend = "You have used a total of $totalAmount of your mana ✨ during this $period.";
            }

        }

        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null
        );
    }
}
