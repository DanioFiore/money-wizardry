<?php

namespace App\Http\Controllers\Api\V1;

use App\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{

    public function inbound(Request $request)
    {
        $command = CommandsController::extractCommandFromText($request->input('message.text', ''));

        // handle command
        if ($command) {
            return CommandsController::activeCommandActions($command);
        }

    }

}
