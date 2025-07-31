<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Telegram\Bot\Laravel\Facades\Telegram;

class BotController extends Controller
{

    public function getUpdates()
    {
        // This method will handle the incoming updates from Telegram.
        // You can process the updates as per your requirements.
        $updates = Http::get('https://api.telegram.org/bot' . env('TELEGRAM_BOT_TOKEN') . '/getMe');

        dd($updates->json());
    }

}
