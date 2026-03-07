<?php

namespace App\Services;

use App\Models\Character;
use App\Traits\SessionValidator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DailyRoulette
{
    use SessionValidator;
    use \App\Traits\LevelManager;

    /**
     * getData returns the current consecutive day the player is on.
     */
    public function getData($sessionkey, $char_id)
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) {
             // Return exactly what validateSession returns (which is status 2 now)
             return $char;
        }

        if ($char->dr_last_spin) {
            $lastSpin = Carbon::parse($char->dr_last_spin);
            
            // If they missed yesterday (difference > 1 day in calendar dates)
            if ($lastSpin->startOfDay()->diffInDays(now()->startOfDay()) > 1) {
                $char->dr_day = 1;
                $char->save();
            }
        } else {
            $char->dr_day = 1;
            $char->save();
        }

        return [
            'status' => 1,
            'error' => 0,
            'result' => $char->dr_day
        ];
    }

    /**
     * newSpinRoulette performs the spin, calculates rewards based on the current day multiplier,
     * updates the database, and returns what was won.
     */
    public function newSpinRoulette($sessionkey, $char_id)
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) {
            return $char;
        }

        if ($char->dr_last_spin) {
            $lastSpin = Carbon::parse($char->dr_last_spin);
            if ($lastSpin->isToday()) {
                return ['status' => 2, 'result' => 'You already spun today.'];
            }
        }

        // Potential rewards map
        // Frame maps to AS timeline, format is item_amount
        $rewards = [
            1  => ['frame' => 1,  'reward' => 'token_10'],
            4  => ['frame' => 4,  'reward' => 'xp_5'],
            7  => ['frame' => 7,  'reward' => 'gold_500'],
            10 => ['frame' => 10, 'reward' => 'token_50'],
            13 => ['frame' => 13, 'reward' => 'xp_10'],
            16 => ['frame' => 16, 'reward' => 'gold_2500'],
            18 => ['frame' => 18, 'reward' => 'gold_10000'],
            21 => ['frame' => 21, 'reward' => 'token_100'],
            24 => ['frame' => 24, 'reward' => 'xp_15'],
        ];
        
        // Randomly select one
        $won = $rewards[array_rand($rewards)];

        $p = explode('_', $won['reward']);
        $type = $p[0];
        $baseVal = (int)$p[1];
        
        $multiplier = $char->dr_day;
        $totalVal = $baseVal * $multiplier;

        if ($type === 'gold') {
            $char->gold += $totalVal;
        } elseif ($type === 'token') {
            $user = $char->user;
            $user->tokens += $totalVal;
            $user->save();
        } elseif ($type === 'xp') {
            // It's a percentage of max HP or next level gap?
            // "xp_5" on day 2 = 10%
            // In our system we do an arbitrary fixed amount of XP or check their required XP
            // Assuming required XP for next level is approx level * 1000 for simplicity
            $reqXpForNext = $this->getExpNeededForLevel($char->level);
            $xpGain = (int)($reqXpForNext * ($totalVal / 100));
            
            $awards = $this->awardXp($char, max(10, $xpGain));
            $char->xp = $awards['xp'];
            $char->level = $awards['level'];
        }

        // Advance to next day for tomorrow
        $char->dr_day = min(7, $char->dr_day + 1);
        $char->dr_last_spin = now();
        $char->save();

        return [
            'status' => 1,
            'error' => 0,
            'reward' => $won['frame'],
            'reward_got' => $won['reward'],
            'xp' => $char->xp,
            'character_level' => $char->level,
        ];
    }
}
