<?php

namespace App\Traits;

use App\Models\Character;

trait LevelManager
{
    /**
     * Returns the XP required to reach the NEXT level from the CURRENT level.
     */
    public function getExpNeededForLevel($level): int
    {
        switch ($level) {
            case 1: return 15;
            case 2: return 304;
            case 3: return 493;
            case 4: return 711;
            case 5: return 961;
            case 6: return 1247;
            case 7: return 1574;
            case 8: return 1945;
            case 9: return 2366;
            case 10: return 2843;
            case 11: return 3382;
            case 12: return 3989;
            case 13: return 4673;
            case 14: return 5542;
            case 15: return 6306;
            case 16: return 7273;
            case 17: return 8537;
            case 18: return 9569;
            case 19: return 10922;
            case 20: return 12433;
            case 21: return 14117;
            case 22: return 15992;
            case 23: return 18080;
            case 24: return 20401;
            case 25: return 22981;
            case 26: return 25845;
            case 27: return 29024;
            case 28: return 32548;
            case 29: return 36454;
            case 30: return 40780;
            case 31: return 45569;
            case 32: return 50867;
            case 33: return 56725;
            case 34: return 63201;
            case 35: return 70354;
            case 36: return 78254;
            case 37: return 86973;
            case 38: return 96593;
            case 39: return 107202;
            case 40: return 118899;
            case 41: return 131790;
            case 42: return 145991;
            case 43: return 161632;
            case 44: return 178850;
            case 45: return 197801;
            case 46: return 218652;
            case 47: return 241587;
            case 48: return 266806;
            case 49: return 294530;
            case 50: return 325000;
            case 51: return 358478;
            case 52: return 395253;
            case 53: return 435640;
            case 54: return 479982;
            case 55: return 528656;
            case 56: return 582073;
            case 57: return 640648;
            case 58: return 704980;
            case 59: return 775497;
            case 60: return 858822;
            case 61: return 973598;
            case 62: return 1030523;
            case 63: return 1132364;
            case 64: return 1243956;
            case 65: return 1366211;
            case 66: return 1500266;
            case 67: return 1646789;
            case 68: return 1807388;
            case 69: return 1983211;
            case 70: return 2175702;
            case 71: return 2386377;
            case 72: return 2616933;
            case 73: return 2869211;
            case 74: return 3145217;
            case 75: return 3447146;
            case 76: return 3777386;
            case 77: return 4138547;
            case 78: return 4533474;
            case 79: return 4965272;
            case 80: return 5437326;
            case 81: return 5953328;
            case 82: return 6517304;
            case 83: return 7133648;
            case 84: return 7807145;
            case 85: return 8543017;
            case 86: return 9346956;
            case 87: return 10512475;
            case 88: return 11184414;
            case 89: return 12232070;
            case 90: return 13376173;
            case 91: return 14625481;
            case 92: return 15825481;
            case 93: return 17422318;
            case 94: return 19336471;
            case 95: return 20879013;
            case 96: return 23921871;
            case 97: return 25599188;
            case 98: return 27225299;
            case 99: return 29753575;
            case 100: return 32500000;
            default: return 999999999; // Cap
        }
    }

    /**
     * Awards XP to a character and handles leveling up.
     * Returns an array with updated level, xp, and level_up flag.
     */
    public function awardXp(Character $char, int $xp_gain): array
    {
        $user = $char->user;
        if ($user && (int)($user->account_type ?? 0) === 2) {
            $xp_gain *= 3;
        }

        $new_xp    = (int) $char->xp + $xp_gain;
        $new_level = (int) $char->level;
        $rank      = (int) ($char->rank ?? 1);
        $level_up  = false;

        $xp_needed = $this->getExpNeededForLevel($new_level);

        while ($new_xp >= $xp_needed && $new_level < 100) {
            // Rank Caps: stop level-up if rank is too low
            $is_capped = false;
            if ($new_level == 20 && $rank < 2) $is_capped = true;
            elseif ($new_level == 40 && $rank < 4) $is_capped = true;
            elseif ($new_level == 60 && $rank < 6) $is_capped = true;
            elseif ($new_level == 80 && $rank < 8) $is_capped = true;

            if ($is_capped) {
                // User is capped at this level.
                $new_xp = $xp_needed - 1;
                break;
            }

            $new_xp -= $xp_needed;
            $new_level++;
            $level_up = true;

            if ($new_level >= 100) {
                $new_level = 100;
                $new_xp = 0; // Or cap at a specific value if preferred
                break;
            }

            $xp_needed = $this->getExpNeededForLevel($new_level);
        }

        // Final safety check for level 100
        if ($new_level >= 100) {
            $new_level = 100;
            $new_xp = 0;
        }

        return [
            'level'    => $new_level,
            'xp'       => $new_xp,
            'level_up' => $level_up,
            'xp_gain'  => $xp_gain, // Return the actual gain for UI display
        ];
    }
}
