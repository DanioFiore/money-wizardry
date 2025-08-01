<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class CommandsController extends Controller
{
    public static function activeCommandActions($command)
    {
        switch ($command) {
            case '/start':
                return response()->json([
                    'message' => 'Welcome to the bot! Use /help to see available commands.'
                ]);
            case '/help':
                return response()->json([
                    'message' => 'Available commands: /start, /help, /status'
                ]);
            case '/status':
                return response()->json([
                    'message' => 'Bot is running smoothly!'
                ]);
            default:
                return response()->json([
                    'message' => "Unknown command: {$command}"
                ], 400);
        }

    }

    public static function extractCommandFromText($text)
    {
        // Extract command from the text, assuming commands start with '/'
        if (preg_match('/\/(\w+)/', $text, $matches)) {
            return '/' . $matches[1];
        }
        
        return null;
    } 
}
