<?php

namespace App\Http\Controllers\Api\V1;

use App\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class TelegramController extends Controller
{

    public function inbound(Request $request)
    {
        return ApiResponse::handle(function() use ($request) {
            // Log the incoming request for debugging
            Log::info('Telegram Inbound Request', [
                'request' => $request->all(),
            ]);

            if ($request->input('entities.type', '') === 'bot_command') {
                Log::info('Telegram command detected');

                // Extract the command from the message text
                $command = CommandsController::extractCommandFromText($request->input('message.text', ''));
                
                // Handle the command
                if (!empty($command)) {
                    return CommandsController::handleCommands($command);
                }

            } else {
                // check the message text to answer
                Log::info('No command found in the message', [
                    'message' => $request->input('message.text', ''),
                ]);
            }

            return 'success';
        });
    }

}
