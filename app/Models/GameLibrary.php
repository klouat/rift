<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameLibrary extends Model
{
    protected $fillable = ['item_id', 'data'];
    protected $casts = ['data' => 'array'];
}
