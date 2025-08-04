<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\TelegramAvailableCommand;

class CommandsController extends Controller
{
    public static function handleCommand(Request $request, string $command)
    { 
        switch ($command) {
            case '/rules':
                return self::handleRulesCommand($request);
            case '/help':
                return self::handleHelpCommand($request);
            case '/register':
                return self::handleRegisterCommand($request);
            case '/hc':
                return self::handleHourlyComparisonCommand($request);
            case '/hmset':
                return self::handleHourlyManaSetCommand($request);
            default:
                return response()->json([
                    'message' => "Unknown command: {$command}"
                ], 400);
        }
    }

    public static function extractCommandFromText($text): string
    {
        // Extract command from the text, assuming commands start with '/'
        if (preg_match('/\/(\w+)/', $text, $matches)) {
            return '/' . $matches[1];
        }

        return '';
    } 

    protected static function handleRulesCommand(Request $request): void
    {
        $textToSend = '';

        // check if the user exist. If so, send a welcome back message
        if (TelegramUser::where('telegram_id', $request->input('message.from.id'))->exists()) {
            $textToSend = 'Welcome back, Wizard 🧙🏼‍♂️. You are already part of the council. Cast 🪄 /help to materialize all available summons.';
        } else {
            // if the user does not exist, ask him to register
            $textToSend = 'Welcome, foreigner. The council of the Wizardry awaits you 🏯. Please cast 🪄 /register to join us.';
        }

        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null // actual message ID if needed to reply to
        );
    }

    protected static function handleRegisterCommand(Request $request): void
    {
        $textToSend = '';

        if (TelegramUser::where('telegram_id', $request->input('message.from.id'))->exists()) {
            $textToSend = 'You are already part of the council, Wizard 🧙🏼‍♂️.';
        } else {
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

            $textToSend = 'You are now part of the council, Wizard 🧙🏼‍♂️. Use /start to begin your journey and learn the rules of the council 🏯.';
        }


        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null
        );
    }

    protected static function handleHelpCommand(Request $request): void
    {
        $textToSend = "🪄 Available summons in the council of Wizardry 🪄\n\n";
        
        $commandsList = TelegramAvailableCommand::where('is_active', true)->get();

        $baseCommands = "";
        $manaCommands = "";
        $infoCommands = "";
        $reportsCommands = "";

        if ($commandsList->isEmpty()) {
            $textToSend = "No available summons at the moment. Please check back later.";
        } else {
            foreach ($commandsList as $command) {

                switch ($command->type) {
                    case 'base':

                        if (empty($baseCommands)) {
                            $baseCommands .= "Base Commands 🏯:\n";
                        }

                        $baseCommands .= "{$command->command} - {$command->description}\n";

                        break;
                    case 'mana':

                        if (empty($manaCommands)) {
                            $manaCommands .= "Mana Commands ✨:\n";
                        }

                        $manaCommands .= "{$command->command} - {$command->description}\n";

                        break;
                    case 'info':

                        if (empty($infoCommands)) {
                            $infoCommands .= "Info Commands 📜:\n";
                        }

                        $infoCommands .= "{$command->command} - {$command->description}\n";

                        break;
                    case 'reports':

                        if (empty($reportsCommands)) {
                            $reportsCommands .= "Reports Commands 📊:\n";
                        }

                        $reportsCommands .= "{$command->command} - {$command->description}\n";

                        break;
                    default:
                        Log::warning('Unknown command type', ['type' => $command->type]);
                        break;
                }

            }

        }

        $textToSend .= $baseCommands . "\n";
        $textToSend .= $manaCommands . "\n";
        $textToSend .= $infoCommands . "\n";
        $textToSend .= $reportsCommands . "\n";

        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null
        );
    }

    protected static function handleHourlyComparisonCommand(Request $request): void
    {
        $telegramUser = TelegramUser::where('telegram_id', $request->input('message.from.id'))->first();

        if (!$telegramUser) {
            $textToSend = 'Welcome, foreigner. The council of the Wizardry awaits you 🏯. Please cast 🪄 /register to join us.';
        } else {
            if (!$telegramUser->hourly_mana) {
                $textToSend = "You have not set your hourly mana gain yet. You can cast 🪄 /hmset followed by the amount of your hourly mana gain ✨ to update it. (Example: /hmset 7.5)";
            } else {
                $textToSend = "Your magic was successfully casted! Your hourly comparison is ";
                $telegramUser->hourly_comparison = !$telegramUser->hourly_comparison;
                $telegramUser->save();
                $textToSend .= $telegramUser->hourly_comparison ? 'enabled ✅' : 'disabled ❌';
    
                if ($telegramUser->hourly_comparison) {
                    $textToSend .= ". Your current hourly mana gain ✨ is {$telegramUser->hourly_mana}.";
                    $textToSend .= " You can cast 🪄 /hmset followed by the amount of your hourly mana gain ✨ to update it. (Example: /hmset 7.5)";
                }

            }
        }


        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null
        );
    }

    protected static function handleHourlyManaSetCommand(Request $request): void
    {
        $telegramUser = TelegramUser::where('telegram_id', $request->input('message.from.id'))->first();

        if (!$telegramUser) {
            $textToSend = 'Welcome, foreigner. The council of the Wizardry awaits you 🏯. Please cast 🪄 /register to join us.';
        } else {
            // Assuming the command is followed by the mana amount
            $mana = str_replace('/hmset ', '', $request->input('message.text', ''));
            $mana = trim($mana);
            $mana = str_replace(',', '.', $mana); // Replace comma with dot for decimal values
            $mana = floatval($mana);

            if ($mana <= 0 || !is_numeric($mana) || empty($mana)) {
                $textToSend = "Please provide a valid hourly mana gain ✨ to the council. You can cast 🪄 /hmset followed by the amount of your hourly mana gain to update it. (Example: /hmset 7.5)";
            } else {
                $telegramUser->hourly_mana = $mana;
                $telegramUser->save();
                $textToSend = "Your hourly mana gain ✨ has been set to {$mana}.";
            }
        }

        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null
        );
    }
}
