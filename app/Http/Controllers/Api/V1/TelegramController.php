<?php

namespace App\Http\Controllers\Api\V1;

use App\ApiResponse;
use App\Models\ManaSpent;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TelegramUtilsController;

class TelegramController extends Controller
{
    public function inbound(Request $request)
    {
        return ApiResponse::handle(function() use ($request) {
            $summon = TelegramUtilsController::extractSummonFromText($request->input('message.text', ''));

            if (!empty($summon)) {
                Log::info('Telegram summon detected');

                // Handle the summon
                return SummonsController::handleSummon($request, $summon);
            } else {
                // check the message text to answer
                return $this->handleManaSpentMessage($request);
            }

            return 'success';
        });
    }

    protected function handleManaSpentMessage(Request $request): void
    {
        // check if the user exists in the DB
        $telegramUser = TelegramUser::where('telegram_id', $request->input('message.from.id'))->first();

        // if the user does not exist, send a message to register
        if (!$telegramUser) {
            $textToSend = env('TELEGRAM_BOT_REGISTER_MSG', 'Registration message not set in .env file. Please set TELEGRAM_BOT_REGISTER_MSG variable.');
        } else {
            $amount = trim($request->input('message.text'));
            // replace comma with dot for decimal point
            $amount = str_replace(',', '.', $amount);
            // remove any non-numeric characters except for the decimal point
            $amount = preg_replace('/[^\d.]/', '', $amount);
            $amount = floatval($amount);

            // check if the amount is valid
            if ($amount <= 0 || !is_numeric($amount) || empty($amount)) {
                $textToSend = 'You cast the wrong spell ❌. I think you missed out on something. Maybe a mana amount? 🧐';
            } else {
                $new_mana_spent = new ManaSpent();
                $new_mana_spent->telegram_id = $telegramUser->telegram_id;
                $new_mana_spent->amount = $amount;
                $new_mana_spent->save();

                if ($telegramUser->time_comparison) {

                    if ($telegramUser->hourly_mana_gain) {
                        // calculate necessary hours and minutes
                        $hoursNeeded = $amount / $telegramUser->hourly_mana_gain;
                        $hours = floor($hoursNeeded);
                        $minutes = round(($hoursNeeded - $hours) * 60);
                        
                        // if minutes are 60 or more, convert to hours
                        if ($minutes >= 60) {
                            $hours++;
                            $minutes = 0;
                        }

                        $hourTranslation = $hours == 1 ? 'hour' : 'hours';
                        $minuteTranslation = $minutes == 1 ? 'minute' : 'minutes';
                        $textToSend = "You have used $amount of your mana ✨ that corresponds to $hours $hourTranslation and $minutes $minuteTranslation of your time ⏳.\n";
                    } else {
                        $textToSend = "You have used $amount of your mana ✨";
                    }

                } else {
                    $textToSend = "You have used $amount of your mana ✨";
                }
            }

        }

        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null
        );
    }
}
