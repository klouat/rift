<?php

namespace App\Services;

use App\Models\Character;
use App\Traits\LevelManager;
use Illuminate\Support\Facades\Log;

class EudemonGarden
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
        $eg = $char->eudemonGarden;

        // Based on ActionScript, total pages are 3, 5 bosses per page = 15 total bosses.
        if (!$eg || $eg->last_reset !== $today) {
            $attempts = array_fill(0, 15, 3);
            if (!$eg) {
                $eg = $char->eudemonGarden()->create([
                    'attempts'   => implode(',', $attempts),
                    'last_reset' => $today,
                ]);
            } else {
                $eg->update([
                    'attempts'   => implode(',', $attempts),
                    'last_reset' => $today,
                ]);
            }
        } else {
            $attempts = explode(',', $eg->attempts);
        }

        $essentials = $char->getInventoryArray('char_essentials');
        // ActionScript populates txt_material with essential_amt
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
     * finishHunting — awards xp/gold and items after the boss fight.
     * Arguments: [char_id, boss_num, battle_code, hash, sessionkey]
     */
    public function finishHunting($char_id, $boss_num, $battle_code, $hash, $sessionkey)
    {
        $char = Character::find((int) $char_id);
        if (!$char) {
            return ['status' => 0, 'error' => 'Character not found.'];
        }

        $boss_idx = (int) $boss_num;
        
        // Reward mapping from ActionScript loadBossData
        $boss_rewards = [
            0 => ['gold' => 30000, 'xp' => 20000, 'pool' => ['wpn_210','material_01','material_198']],
            1 => ['gold' => 35000, 'xp' => 23000, 'pool' => ['wpn_212','material_01','material_02','material_198']],
            2 => ['gold' => 38000, 'xp' => 25000, 'pool' => ['wpn_214','material_01','material_02','material_198']],
            3 => ['gold' => 40000, 'xp' => 30000, 'pool' => ['wpn_216','wpn_218','material_01','material_02','material_03','material_198']],
            4 => ['gold' => 49000, 'xp' => 35000, 'pool' => ['wpn_220','material_01','material_02','material_03','material_198']],
            5 => ['gold' => 55000, 'xp' => 38000, 'pool' => ['wpn_222','material_01','material_02','material_03','material_04','material_198']],
            6 => ['gold' => 57000, 'xp' => 40000, 'pool' => ['wpn_224','material_01','material_02','material_03','material_04','material_198']],
            7 => ['gold' => 62000, 'xp' => 48000, 'pool' => ['wpn_226','material_01','material_02','material_03','material_04','material_05','material_198']],
            8 => ['gold' => 72000, 'xp' => 56000, 'pool' => ['wpn_228','material_01','material_02','material_03','material_04','material_05','material_06','material_198']],
            9 => ['gold' => 100000, 'xp' => 75000, 'pool' => ['wpn_230','material_01','material_02','material_03','material_04','material_05','material_06','material_198']],
            10 => ['gold' => 150000, 'xp' => 100000, 'pool' => ['wpn_240','material_01','material_02','material_03','material_04','material_05','material_06','material_07','material_198']],
            11 => ['gold' => 200000, 'xp' => 150000, 'pool' => ['wpn_242','material_01','material_02','material_03','material_04','material_05','material_06','material_07','material_08','material_198']],
        ];

        $reward = $boss_rewards[$boss_idx] ?? [
            'gold' => 10000,
            'xp'   => 5000,
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

        // Item rewards logic (Rolling 3 times, same as Hunting House)
        $granted_items = [];
        $pool = $reward['pool'];
        for ($i = 0; $i < 3; $i++) {
            $item_id = $pool[array_rand($pool)];
            $category = 'char_materials';
            if (str_starts_with($item_id, 'wpn_')) $category = 'char_weapons';
            elseif (str_starts_with($item_id, 'mat_') || str_starts_with($item_id, 'material_')) $category = 'char_materials';
            
            $char->addToInventory($category, $item_id, 1);
            $granted_items[] = $item_id;
        }

        $char->save();

        // Update attempts
        $eg = $char->eudemonGarden;
        if ($eg) {
            $attempts = explode(',', $eg->attempts);
            if (isset($attempts[$boss_idx]) && (int)$attempts[$boss_idx] > 0) {
                $attempts[$boss_idx] = (int)$attempts[$boss_idx] - 1;
                $eg->update(['attempts' => implode(',', $attempts)]);
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
                $granted_items,
                $level_up,
            ],
        ];
    }

    /**
     * buyTriesNew — resets boss attempts.
     * Arguments: [sessionkey, char_id, index]
     * index 0: Essentials (essential_109), index 1: Tokens (100)
     */
    public function buyTriesNew($sessionkey, $char_id, $index)
    {
        $char = Character::find((int) $char_id);
        if (!$char) return ['status' => 0, 'result' => 'Character not found.'];

        $user = $char->user;
        $cost_tokens = 100;

        if ($index == 0) {
            // Essential for Eudemon Garden is 109
            if (!$char->removeFromInventory('char_essentials', 'essential_109', 1)) {
                return ['status' => 0, 'result' => "Not enough essentials."];
            }
        } else {
            if ($user->tokens < $cost_tokens) {
                return ['status' => 0, 'result' => "Not enough tokens."];
            }
            $user->tokens -= $cost_tokens;
            $user->save();
        }

        $char->save();

        $eg = $char->eudemonGarden;
        if ($eg) {
            $attempts = array_fill(0, 15, 3);
            $eg->update(['attempts' => implode(',', $attempts)]);
        }

        return [
            'status' => 1,
            'result' => 'Boss attempts reset!'
        ];
    }
}
