<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    protected $fillable = [
        'char_id', 'pet_swf', 'pet_name', 'pet_level', 'pet_xp',
        'pet_favorite', 'pet_mp', 'pet_skills', 'pet_weapon',
        'pet_back_item', 'pet_emblem'
    ];

    public function character()
    {
        return $this->belongsTo(Character::class, 'char_id');
    }
}
