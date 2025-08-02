<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramAvailableCommand extends Model
{
    protected $table = 'telegram__available_commands';

    protected $fillable = [
        'command',
        'description',
        'is_active',
        'type',
    ];
}
