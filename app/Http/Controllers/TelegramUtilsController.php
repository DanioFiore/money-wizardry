<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TelegramUtilsController extends Controller
{
    public static function extractSummonFromText($text): string
    {
        // Extract summon (command) from the text, assuming commands start with '/'
        if (preg_match('/\/(\w+)/', $text, $matches)) {
            return '/' . $matches[1];
        }

        return '';
    } 
}
