<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    protected $table = 'telegram__users';
    protected $fillable = [
        'telegram_id',
        'username',
        'first_name',
        'last_name',
        'user_id',
        'hourly_mana',
        'monthly_mana',
        'yearly_mana',
        'hourly_comparison',
    ];
}
