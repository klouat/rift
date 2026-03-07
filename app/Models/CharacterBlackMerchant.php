<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterBlackMerchant extends Model
{
    protected $table = 'character_black_merchant';

    protected $fillable = [
        'char_id',
        'package_id',
        'refreshed_at'
    ];

    protected $casts = [
        'refreshed_at' => 'datetime'
    ];
}
