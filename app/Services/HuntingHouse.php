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

        $today = now()->toDateString();
        $hh = $char->huntingHouse;

        if (!$hh || $hh->last_reset !== $today) {
            // New day or new entry: Reset to 3 attempts for each boss (using 40 indices)
            $attempts = array_fill(0, 40, 3);
            if (!$hh) {
                $hh = $char->huntingHouse()->create([
                    'attempts'   => implode(',', $attempts),
                    'last_reset' => $today,
                ]);
            } else {
                $hh->update([
                    'attempts'   => implode(',', $attempts),
                    'last_reset' => $today,
                ]);
            }
        } else {
            $attempts = explode(',', $hh->attempts);
        }
        
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

        $hh = $char->huntingHouse;
        if ($hh) {
            $attempts = array_fill(0, 40, 3);
            $hh->update(['attempts' => implode(',', $attempts)]);
        }

        return [
            'status' => 1,
            'result' => 'Boss attempts reset!'
        ];
    }

    /**
     * finishHunting — awards xp/gold and items after the boss fight.
     * Arguments: [char_id, boss_num, battle_code, hash, sessionkey]
     */
    public function finishHunting($char_id, $boss_num, $battle_code, $hash, $sessionkey)
    {
        $char = \App\Models\Character::find((int) $char_id);
        if (!$char) {
            return ['status' => 0, 'error' => 'Character not found.'];
        }

        $boss_idx = (int) $boss_num;
        
        // Reward mapping from ActionScript
        $boss_rewards = [
            0 => ['gold' => 4000, 'xp' => 1491, 'pool' => ['wpn_201','material_01','material_02','material_198']],
            1 => ['gold' => 5000, 'xp' => 4078, 'pool' => ['wpn_202','material_01','material_02','material_03','material_198']],
            2 => ['gold' => 6000, 'xp' => 6745, 'pool' => ['wpn_203','material_01','material_02','material_03','material_198']],
            3 => ['gold' => 7000, 'xp' => 10602, 'pool' => ['wpn_204','wpn_205','material_01','material_02','material_03','material_04','material_198']],
            4 => ['gold' => 14000, 'xp' => 17834, 'pool' => ['wpn_206','material_01','material_02','material_03','material_04','material_05','material_198']],
            5 => ['gold' => 20000, 'xp' => 48750, 'pool' => ['wpn_207','material_01','material_02','material_03','material_04','material_05','material_198']],
            6 => ['gold' => 25000, 'xp' => 58152, 'pool' => ['wpn_208','material_01','material_02','material_03','material_04','material_05','material_198']],
            7 => ['gold' => 30000, 'xp' => 68225, 'pool' => ['wpn_209','material_01','material_02','material_03','material_04','material_05','material_198']],
            8 => ['gold' => 35000, 'xp' => 85435, 'pool' => ['wpn_232','material_01','material_02','material_03','material_04','material_05','material_06','material_198']],
            9 => ['gold' => 40000, 'xp' => 102213, 'pool' => ['wpn_234','material_01','material_02','material_03','material_04','material_05','material_06','material_198']],
            10 => ['gold' => 50000, 'xp' => 151829, 'pool' => ['wpn_236','material_01','material_02','material_03','material_04','material_05','material_06','material_07','material_198']],
            11 => ['gold' => 75000, 'xp' => 189147, 'pool' => ['wpn_238','material_01','material_02','material_03','material_04','material_05','material_06','material_07','material_08','material_198']],
        ];

        $reward = $boss_rewards[$boss_idx] ?? [
            'gold' => (200 * $boss_idx) + 500,
            'xp'   => (500 * $boss_idx) + 1000,
            'pool' => ['material_01', 'material_198']
        ];

        $xp_gain   = $reward['xp'];
        $gold_gain = $reward['gold'];

        $awards = $this->awardXp($char, $xp_gain);
        $new_xp = $awards['xp'];
        $new_level = $awards['level'];
        $level_up = $awards['level_up'];
        $actual_xp_gain = $awards['xp_gain'];

        $char->xp = $new_xp;
        $char->level = $new_level;
        $char->gold = (int) $char->gold + $gold_gain;

        // Item rewards logic
        $granted_items = [];
        
        // Random items from pool (Roll 3 times, same item allowed)
        $pool = $reward['pool'];
        for ($i = 0; $i < 3; $i++) {
            $item_id = $pool[array_rand($pool)];
            
            // Determine category
            $category = 'char_materials';
            if (str_starts_with($item_id, 'wpn_')) $category = 'char_weapons';
            elseif (str_starts_with($item_id, 'mat_') || str_starts_with($item_id, 'material_')) $category = 'char_materials';
            
            $char->addToInventory($category, $item_id, 1);
            $granted_items[] = $item_id;
        }

        $char->save();

        // Update attempts
        $hh = $char->huntingHouse;
        if ($hh) {
            $attempts = explode(',', $hh->attempts);
            $idx = (int)$boss_num;
            if (isset($attempts[$idx]) && (int)$attempts[$idx] > 0) {
                $attempts[$idx] = (int)$attempts[$idx] - 1;
                $hh->update(['attempts' => implode(',', $attempts)]);
            }
        }

        return [
            'status'   => 1,
            'level'    => $new_level,
            'xp'       => $new_xp,
            'level_up' => $level_up,
            'result'   => [
                (string) $actual_xp_gain,
                (string) $gold_gain,
                $granted_items, // Sent list of granted items
                $level_up,
            ],
        ];
    }
}

