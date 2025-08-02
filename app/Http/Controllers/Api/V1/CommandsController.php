<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommandsController extends Controller
{
    public static function handleCommands(Request $request, string $command)
    { 
        switch ($command) {
            case '/start':
                Log::info('Telegram handle start');

                return self::handleStartCommand($request);
            case '/help':
                Log::info('Telegram handle help');

                return self::handleHelpCommand();
            case '/register':
                Log::info('Telegram handle register');

                return self::handleRegisterCommand($request);
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

    protected static function handleStartCommand(Request $request)
    {
        Log::info('Handling /start command');

        $textToSend = '';

        // check if the user exist. If so, send a welcome back message
        if (TelegramUser::where('telegram_id', $request->input('message.from.id'))->exists()) {
            Log::info('User exists, sending welcome back message');

            $textToSend = 'Welcome back! You are already registered. Use /help to see available commands or just write a price to register a new transaction.';
        } else {
            Log::info('User does not exist, sending registration message');

            // if the user does not exist, ask him to register
            $textToSend = 'Welcome Money Wizardry! Please register with /register command.';
        }

        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null // actual message ID if needed to reply to
        );
    }

    protected static function handleRegisterCommand(Request $request)
    {
        Log::info('Handling /register command');

        $textToSend = '';

        if (TelegramUser::where('telegram_id', $request->input('message.from.id'))->exists()) {
            Log::info('User already registered, sending message');

            $textToSend = 'You are already registered.';
        } else {
            Log::info('Registering new user');

            $telegramId = $request->input('message.from.id');
            $username = $request->input('message.from.username', 'Unknown User');
            
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

            $textToSend = 'Welcome, wizard 🧙🏼‍♂️.';
        }


        app('telegramBot')->sendMessage(
            $textToSend,
            $request->input('message.chat.id'),
            null // actual message ID if needed to reply to
        );
    }

    protected static function handleHelpCommand()
    {
        // Logic for handling the /help command
        return response()->json([
            'message' => 'Available commands: /start, /help'
        ]);
    }
}
