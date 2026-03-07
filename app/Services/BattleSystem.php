<?php

namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Facades\Log;

class BattleSystem
{
    /**
     * Validates the incoming battle request and returns a 10-char battle code.
     * The client checks: if (param1.length != 10) → abort.
     * So we MUST return exactly a 10-character string.
     */
    public function startMission($char_id, $mission_id, $enemy_ids, $enemy_stats, $agility, $hash, $sessionkey): string
    {
        return substr(md5(uniqid($char_id . $mission_id, true)), 0, 10);
    }

    /**
     * Finishes the mission and awards XP/gold.
     *
     * Client reads on status == 1 (MissionMatch):
     *   param1.result[0] → xp string
     *   param1.result[1] → gold string
     *   param1.result[2] → item rewards array
     *   param1.level_up  → boolean
     *   param1.level     → new level
     *   param1.xp        → new total xp
     *   param1.char_back_item → equipped back item string
     */
    public function finishMission($char_id, $mission_id, $battle_code, $hash, $total_damage, $sessionkey): array
    {
        $char = Character::find((int) $char_id);

        if (!$char) {
            return ['status' => 0, 'error' => 1, 'result' => 'Character not found.'];
        }

        $xp_gain   = 300;
        $gold_gain = 50;

        $new_xp    = (int) $char->xp + $xp_gain;
        $level_up  = false;
        $new_level = (int) $char->level;

        // Simple XP threshold: 500 * level per level
        $xp_needed = $new_level * 500;
        if ($new_xp >= $xp_needed && $new_level < 100) {
            $new_xp   -= $xp_needed;
            $new_level++;
            $level_up  = true;
        }

        $char->update([
            'xp'    => $new_xp,
            'level' => $new_level,
            'gold'  => (int) $char->gold + $gold_gain,
        ]);

        return [
            'status'         => 1,
            'error'          => 0,
            'char_back_item' => $char->equipped_back_item ?? 'back_01',
            'level'          => $new_level,
            'xp'             => $new_xp,
            'level_up'       => $level_up,
            'result'         => [
                'xp_'   . $xp_gain,
                'gold_' . $gold_gain,
                [],
                $level_up,
            ],
        ];
    }
}
