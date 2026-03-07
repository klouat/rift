<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterItem extends Model
{
    protected $fillable = ['character_id', 'item_type', 'item_id', 'quantity'];

    public function character()
    {
        return $this->belongsTo(Character::class, 'character_id');
    }
}
