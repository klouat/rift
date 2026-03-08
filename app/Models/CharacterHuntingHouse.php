<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterHuntingHouse extends Model
{
    protected $table = 'character_hunting_house';

    protected $fillable = [
        'char_id',
        'attempts',
        'last_reset'
    ];

    public function character()
    {
        return $this->belongsTo(Character::class, 'char_id');
    }
}
