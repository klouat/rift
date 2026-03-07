<?php

namespace App\Services;

use App\Models\Character;

class EudemonGarden
{
    use \App\Traits\LevelManager;

    /**
     * getData — returns boss attempts.
     * Arguments: [sessionkey, char_id]
     */
    public function getData($sessionkey, $char_id)
    {
        $char = Character::find((int) $char_id);
        if (!$char) {
            return ['status' => 0, 'error_code' => 'character_not_found'];
        }

        // Return 40 indices to be safe, all with 1 attempt available.
        $attempts = array_fill(0, 40, 1); 

        return [
            'status' => 1,
            'data'   => implode(',', $attempts),
        ];
    }

    /**
     * startHunting — starts the boss fight.
     * Arguments: [char_id, boss_num, sessionkey]
     */
    public function startHunting($char_id, $boss_num, $sessionkey)
    {
        $char = Character::find((int) $char_id);
        if (!$char) return null;

        return substr(md5(uniqid($char_id . $boss_num, true)), 0, 10);
    }

    /**
     * finishHunting — awards xp/gold after the boss fight.
     * Arguments: [char_id, boss_num, battle_code, hash, sessionkey]
     */
    public function finishHunting($char_id, $boss_num, $battle_code, $hash, $sessionkey)
    {
        $char = Character::find((int) $char_id);
        if (!$char) {
            return ['status' => 0, 'error' => 'Character not found.'];
        }

        $xp_gain   = (500 * (int) $boss_num) + 1200;
        $gold_gain = (200 * (int) $boss_num) + 600;

        $awards = $this->awardXp($char, $xp_gain);
        $new_xp = $awards['xp'];
        $new_level = $awards['level'];
        $level_up = $awards['level_up'];
        $actual_xp_gain = $awards['xp_gain'];

        $char->xp = $new_xp;
        $char->level = $new_level;
        $char->gold = (int) $char->gold + $gold_gain;
        $char->save();

        return [
            'status'   => 1,
            'level'    => $new_level,
            'xp'       => $new_xp,
            'level_up' => $level_up,
            'result'   => [
                (string) $actual_xp_gain,
                (string) $gold_gain,
                [], // Items array
                $level_up,
            ],
        ];
    }
}
