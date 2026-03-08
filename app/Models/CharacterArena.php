<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterArena extends Model
{
    protected $fillable = [
        'char_id',
        'stamina',
        'max_stamina',
        'trophies',
        'enemy_id',
        'first_open',
        'village_changed',
        'claimed_trophy_rewards',
        'last_stamina_reset'
    ];

    protected $casts = [
        'last_stamina_reset' => 'datetime',
        'claimed_trophy_rewards' => 'array'
    ];

    public function character()
    {
        return $this->belongsTo(Character::class, 'char_id');
    }
}
