<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterEudemonGarden extends Model
{
    protected $table = 'character_eudemon_garden';

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
