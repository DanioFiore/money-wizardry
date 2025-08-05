<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManaSpent extends Model
{
    protected $table = 'mana__spent';
    protected $fillable = [
        'user_id',
        'telegram_id',
        'amount',
    ];
}
