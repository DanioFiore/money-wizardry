<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramAvailableSummon extends Model
{
    protected $table = 'telegram__available_summons';

    protected $fillable = [
        'summon',
        'description',
        'is_active',
        'type',
    ];
}
