<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HanamiEvent2026 extends Model
{
    protected $table = 'hanami_event_2026';

    protected $fillable = [
        'char_id', 'energy', 'max_energy', 'last_energy_refill',
        'can_claim_free_gift', 'total_draws', 'battle_kills',
        'battle_claims', 'gacha_claims', 'tasks_status',
        'pending_boss_idx', 'pack_0', 'pack_1', 'pack_2',
        'deal_0', 'deal_1', 'deal_2', 'deal_3', 'deal_4', 'deal_5'
    ];

    protected $casts = [
        'battle_kills' => 'array',
        'battle_claims' => 'array',
        'gacha_claims' => 'array',
        'tasks_status' => 'array',
        'last_energy_refill' => 'datetime',
        'can_claim_free_gift' => 'boolean'
    ];
}

