<?php

namespace App\Http\Controllers\Api\V1;

use App\ApiResponse;
use App\Models\Transaction;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class TelegramController extends Controller
{
    public function inbound(Request $request)
    {
        return ApiResponse::handle(function() use ($request) {
            $command = CommandsController::extractCommandFromText($request->input('message.text', ''));

            if (!empty($command)) {
                Log::info('Telegram command detected');
                
                // Handle the command
                return CommandsController::handleCommand($request, $command);
            } else {
                // check the message text to answer
                return $this->handleTransactionMessage($request);
            }

            return 'success';
        });
    }

    protected function handleTransactionMessage(Request $request): void
    {
        Log::info('Handling transaction message', [
            'message' => $request->input('message.text'),
        ]);

        $telegramUser = TelegramUser::where('telegram_id', $request->input('message.from.id'))->first();

        if (!$telegramUser) {
            Log::warning('Telegram user not found', [
                'telegram_id' => $request->input('message.from.id'),
            ]);

            $textToSend = 'Welcome, foreigner. The council of the Wizardry awaits you 🏯. Please cast 🪄 /register to join us.';
        } else {
            Log::info('Telegram user found', [
                'telegram_id' => $telegramUser->telegram_id,
                'username' => $telegramUser->username,
            ]);

            $amount = trim($request->input('message.text'));
            $amount = str_replace(['$', '€', '£'], '', $amount); // Remove currency symbols
            $amount = str_replace(',', '.', $amount); // Replace comma with dot for decimal point
            $amount = preg_replace('/[^\d.]/', '', $amount); // Remove any non-numeric characters except for the decimal point
            $amount = floatval($amount);

            if ($amount <= 0) {
                Log::warning('Invalid transaction amount', [
                    'amount' => $amount,
                ]);

                $textToSend = 'Your magic is not working. I think you missed out on something. Maybe a transaction amount? Please try again with a valid amount.';
            } else {
                Log::info('Valid transaction amount received', [
                    'amount' => $amount,
                ]);

                $new_transaction = new Transaction();
                $new_transaction->telegram_id = $telegramUser->telegram_id;
                $new_transaction->amount = $amount;
                $new_transaction->save();

                if ($telegramUser->hourly_comparison) {

                    if ($telegramUser->hourly_mana) {
                        // Calcolo ore necessarie
                        $hoursNeeded = $amount / $telegramUser->hourly_mana;
                        
                        // Conversione in ore e minuti
                        $hours = floor($hoursNeeded);
                        $minutes = round(($hoursNeeded - $hours) * 60);
                        
                        // Se i minuti sono 60, aggiungi un'ora
                        if ($minutes >= 60) {
                            $hours++;
                            $minutes = 0;
                        }

                        $textToSend = "You have casted a transaction amount of $amount. You have used $hours hour(s) and $minutes minute(s) of your time to cast this spell.\n";
                    } else {
                        $textToSend = "You have casted a transaction amount of $amount. The council of the Wizardry will take care of it. 🏦";
                    }

                } else {
                    $textToSend = "You have casted a transaction amount of $amount. The council of the Wizardry will take care of it. 🏦";
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
