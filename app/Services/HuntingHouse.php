<?php

namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Facades\Log;
use App\Traits\LevelManager;

class HuntingHouse
{
    use LevelManager;
    /**
     * getData — returns boss attempts and essential amount.
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
        
        $essentials = $char->getInventoryArray('char_essentials');
        // Return 108 (HH) or 110 (Soul) depending on what's available? 
        // We'll just return 108 for now.
        $amt = $essentials['essential_108'] ?? 0;

        return [
            'status'        => 1,
            'data'          => implode(',', $attempts),
            'essential_amt' => (string) $amt
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

        // Code exactly 10 chars
        return substr(md5(uniqid($char_id . $boss_num, true)), 0, 10);
    }

    /**
     * buyTriesNew — resets boss attempts.
     * Arguments: [sessionkey, char_id, index]
     * index 0: Essentials, index 1: Tokens
     */
    public function buyTriesNew($sessionkey, $char_id, $index)
    {
        $char = Character::find((int) $char_id);
        if (!$char) return ['status' => 0, 'result' => 'Character not found.'];

        $user = $char->user;

        if ($index == 0) {
            // Try 108 or 110
            if (!$char->removeFromInventory('char_essentials', 'essential_108', 1)) {
                if (!$char->removeFromInventory('char_essentials', 'essential_110', 1)) {
                    return ['status' => 0, 'result' => "Not enough essentials."];
                }
            }
        } else {
            // 100 Tokens
            if ($user->tokens < 100) {
                return ['status' => 0, 'result' => "Not enough tokens."];
            }
            $user->tokens -= 100;
            $user->save();
        }

        $char->save();

        return [
            'status' => 1,
            'result' => 'Boss attempts reset!'
        ];
    }

    /**
     * finishHunting — awards xp/gold after the boss fight.
     * Arguments: [char_id, boss_num, battle_code, hash, sessionkey]
     */
    public function finishHunting($char_id, $boss_num, $battle_code, $hash, $sessionkey)
    {
        $char = \App\Models\Character::find((int) $char_id);
        if (!$char) {
            return ['status' => 0, 'error' => 'Character not found.'];
        }

        // Simple rewards for bosses.
        $xp_gain   = (500 * (int) $boss_num) + 1000;
        $gold_gain = (200 * (int) $boss_num) + 500;

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

