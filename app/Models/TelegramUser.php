<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    protected $fillable = [
        'telegram_id',
        'username',
        'first_name',
        'last_name',
        'user_id',
        'hourly_salary',
        'monthly_salary',
        'yearly_salary',
        'hourly_comparison',
    ];
}
