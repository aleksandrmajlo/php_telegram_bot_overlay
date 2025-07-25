<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramPhoto extends Model
{
    protected $fillable = [
        'name',
        'telegram_user_id',
        'file_id',
        'file_path',
    ];
}
