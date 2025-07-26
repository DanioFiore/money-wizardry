<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Telegram\Bot\Laravel\Facades\Telegram;

class BotController extends Controller
{
    public function test()
    {
        return Telegram::getMe();
    }
}
