<?php

namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Facades\Log;

class BattleSystem
{
    use \App\Traits\SessionValidator;
    use \App\Traits\LevelManager;

    /**
     * Validates the incoming battle request and returns a 10-char battle code.
     * The client checks: if (param1.length != 10) → abort.
     * So we MUST return exactly a 10-character string.
     */
    public function startMission($char_id, $mission_id, $enemy_ids, $enemy_stats, $agility, $hash, $sessionkey)
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

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
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        // Default rewards if mission not found
        $xp_gain   = 300;
        $gold_gain = 50;
        $tp_gain   = 0;
        $rewards   = [];

        $libPath = base_path('missionLibrary.json');
        if (file_exists($libPath)) {
            $lib = json_decode(file_get_contents($libPath), true);
            $missions = $lib['savedMissionLibrary'] ?? [];
            foreach ($missions as $m) {
                if (($m['item_id'] ?? '') == $mission_id) {
                    $effects    = $m['effects'] ?? [];
                    $xp_gain    = (int) ($effects['msn_reward_xp'] ?? $xp_gain);
                    $gold_gain  = (int) ($effects['msn_reward_gold'] ?? $gold_gain);
                    $tp_gain    = (int) ($effects['msn_reward_tp'] ?? 0);
                    $rewards    = $effects['msn_rewards'] ?? [];
                    break;
                }
            }
        }

        $awards = $this->awardXp($char, $xp_gain);
        $new_xp = $awards['xp'];
        $new_level = $awards['level'];
        $level_up = $awards['level_up'];
        $actual_xp_gain = $awards['xp_gain'];

        $char->xp    = $new_xp;
        $char->level = $new_level;
        $char->gold  = (int) $char->gold + $gold_gain;
        $char->tp    = (int) ($char->tp ?? 0) + $tp_gain;
        $char->save();

        // Process item rewards
        foreach ($rewards as $reward_id) {
            $column = 'char_items'; // fallback
            if (str_starts_with($reward_id, 'essential_')) $column = 'char_essentials';
            else if (str_starts_with($reward_id, 'material_')) $column = 'char_materials';
            else if (str_starts_with($reward_id, 'wpn_')) $column = 'char_weapons';
            else if (str_starts_with($reward_id, 'back_')) $column = 'char_back_items';
            else if (str_starts_with($reward_id, 'accessory_')) $column = 'char_accessories';
            else if (str_starts_with($reward_id, 'set_')) $column = 'char_sets';
            else if (str_starts_with($reward_id, 'hair_')) $column = 'char_hairs';

            $char->addToInventory($column, $reward_id, 1);
        }

        return [
            'status'         => 1,
            'error'          => 0,
            'char_back_item' => $char->equipped_back_item ?? 'back_01',
            'level'          => $new_level,
            'xp'             => $new_xp,
            'level_up'       => $level_up,
            'result'         => [
                '' . $actual_xp_gain,
                '' . $gold_gain,
                $rewards,
                $level_up,
            ],
        ];
    }
}
