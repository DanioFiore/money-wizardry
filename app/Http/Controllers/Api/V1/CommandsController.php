<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Services\TelegramBot;
use Illuminate\Support\Facades\Log;

class CommandsController extends Controller
{
    public static function handleCommands($command)
    { 
        switch ($command) {
            case '/start':
                // Log the incoming request for debugging
                Log::info('Telegram handle start');

                return self::handleStartCommand();
            case '/help':
                return self::handleHelpCommand();
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

    protected static function handleStartCommand()
    {
        Log::info('Handling /start command');

        app('telegramBot')->sendMessage(
            'Welcome to the bot! Use /help to see available commands.',
            '268029167', // Replace with actual chat ID
            '29' // Replace with actual message ID if needed
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
