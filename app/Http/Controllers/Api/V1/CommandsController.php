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
                Log::info('Telegram handle rules');

                return self::handleRulesCommand($request);
            case '/help':
                Log::info('Telegram handle help');

                return self::handleHelpCommand($request);
            case '/register':
                Log::info('Telegram handle register');

                return self::handleRegisterCommand($request);
            case '/hc':
                Log::info('Telegram handle hourly comparison');

                return self::handleHourlyComparisonCommand($request);
            case '/hmset':
                Log::info('Telegram handle hourly mana set');

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
        Log::info('Handling /start command');

        $textToSend = '';

        // check if the user exist. If so, send a welcome back message
        if (TelegramUser::where('telegram_id', $request->input('message.from.id'))->exists()) {
            Log::info('User exists, sending welcome back message');

            $textToSend = 'Welcome back, Wizard 🧙🏼‍♂️. You are already part of the council. Cast 🪄 /help to materialize all available summons.';
        } else {
            Log::info('User does not exist, sending registration message');

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
        Log::info('Handling /register command');

        $textToSend = '';

        if (TelegramUser::where('telegram_id', $request->input('message.from.id'))->exists()) {
            Log::info('User already registered, sending message');

            $textToSend = 'You are already part of the council, Wizard 🧙🏼‍♂️.';
        } else {
            Log::info('Registering new user');
            
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
        Log::info('Help command requested');

        $textToSend = "🪄 Available summons in the council of Wizardry 🪄\n\n";
        
        $commandsList = TelegramAvailableCommand::where('is_active', true)->get();

        $baseCommands = "";
        $manaCommands = "";
        $infoCommands = "";
        $reportsCommands = "";

        if ($commandsList->isEmpty()) {
            Log::info('No active commands found, sending default message');
            $textToSend = "No available summons at the moment. Please check back later.";
        } else {
            Log::info('Active commands found, preparing response text');

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
        Log::info('Handling hourly comparison command');

        $user = TelegramUser::where('telegram_id', $request->input('message.from.id'))->first();

        if (!$user) {
            Log::info('User not found, sending registration message');
            $textToSend = 'Welcome, foreigner. The council of the Wizardry awaits you 🏯. Please cast 🪄 /register to join us.';
        } else {
            Log::info('User found, checking hourly comparison');

            if (!$user->hourly_mana) {
                Log::info('User has not set hourly mana gain, sending message');
                $textToSend = "You have not set your hourly mana gain yet. You can cast 🪄 /hmset followed by the amount of your hourly mana gain ✨ to update it. (Example: /hmset 7.5)";
            } else {
                $textToSend = "Your magic was successfully casted! Your hourly comparison is ";
                $user->hourly_comparison = !$user->hourly_comparison;
                $user->save();
                $textToSend .= $user->hourly_comparison ? 'enabled ✅' : 'disabled ❌';
    
                if ($user->hourly_comparison) {
                    $textToSend .= ". Your current hourly mana gain ✨ is {$user->hourly_mana}.";
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
        Log::info('Handling hourly mana set command');

        $user = TelegramUser::where('telegram_id', $request->input('message.from.id'))->first();

        if (!$user) {
            Log::info('User not found, sending registration message');
            $textToSend = 'Welcome, foreigner. The council of the Wizardry awaits you 🏯. Please cast 🪄 /register to join us.';
        } else {
            Log::info('User found, setting hourly salary');

            // Assuming the command is followed by the mana amount
            $mana = str_replace('/hmset ', '', $request->input('message.text', ''));
            $mana = trim($mana);
            $mana = str_replace(',', '.', $mana); // Replace comma with dot for decimal values
            $mana = floatval($mana);

            if ($mana <= 0 || !is_numeric($mana) || empty($mana)) {
                $textToSend = "Please provide a valid hourly mana gain ✨ to the council. You can cast 🪄 /hmset followed by the amount of your hourly mana gain to update it. (Example: /hmset 7.5)";
            } else {
                $user->hourly_mana = $mana;
                $user->save();
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
