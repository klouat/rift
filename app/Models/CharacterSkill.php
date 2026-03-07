<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterSkill extends Model
{
    protected $fillable = ['character_id', 'skill_id', 'quantity'];

    public function character()
    {
        return $this->belongsTo(Character::class, 'character_id');
    }
}
