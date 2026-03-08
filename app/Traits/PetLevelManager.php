<?php

namespace App\Traits;

use App\Models\Pet;
use App\Models\Character;

trait PetLevelManager
{
    /**
     * Returns the XP required to reach the NEXT level from the CURRENT level for a pet.
     */
    public function getPetExpNeededForLevel($level): int
    {
        switch ($level) {
            case 1: return 28;
            case 2: return 61;
            case 3: return 99;
            case 4: return 142;
            case 5: return 192;
            case 6: return 249;
            case 7: return 315;
            case 8: return 389;
            case 9: return 473;
            case 10: return 569;
            case 11: return 676;
            case 12: return 798;
            case 13: return 935;
            case 14: return 1088;
            case 15: return 1261;
            case 16: return 1455;
            case 17: return 1671;
            case 18: return 1914;
            case 19: return 2184;
            case 20: return 2487;
            case 21: return 2823;
            case 22: return 3198;
            case 23: return 3616;
            case 24: return 4080;
            case 25: return 4596;
            case 26: return 5196;
            case 27: return 5805;
            case 28: return 6510;
            case 29: return 7291;
            case 30: return 8156;
            case 31: return 9114;
            case 32: return 10173;
            case 33: return 11345;
            case 34: return 12640;
            case 35: return 14071;
            case 36: return 15651;
            case 37: return 17395;
            case 38: return 19319;
            case 39: return 21440;
            case 40: return 23780;
            case 41: return 26538;
            case 42: return 29198;
            case 43: return 32326;
            case 44: return 35570;
            case 45: return 39560;
            case 46: return 43730;
            case 47: return 48317;
            case 48: return 53361;
            case 49: return 58906;
            case 50: return 65000;
            case 51: return 71696;
            case 52: return 79051;
            case 53: return 87128;
            case 54: return 95996;
            case 55: return 105731;
            case 56: return 116415;
            case 57: return 128137;
            case 58: return 140996;
            case 59: return 155099;
            case 60: return 170564;
            case 61: return 187520;
            case 62: return 206105;
            case 63: return 226473;
            case 64: return 248791;
            case 65: return 273242;
            case 66: return 300025;
            case 67: return 329358;
            case 68: return 361478;
            case 69: return 396644;
            case 70: return 435140;
            case 71: return 477275;
            case 72: return 523387;
            case 73: return 573842;
            case 74: return 629043;
            case 75: return 689429;
            case 76: return 755477;
            case 77: return 827709;
            case 78: return 906695;
            case 79: return 993054;
            case 80: return 1087465;
            case 81: return 1197465;
            case 82: return 1317265;
            case 83: return 1477265;
            case 84: return 1627265;
            case 85: return 1797265;
            case 86: return 1987265;
            case 87: return 2147265;
            case 88: return 2327265;
            case 89: return 2517265;
            case 90: return 2737265;
            case 91: return 2917265;
            case 92: return 3297265;
            case 93: return 3477265;
            case 94: return 3727265;
            case 95: return 3917265;
            case 96: return 4197265;
            case 97: return 4417265;
            case 98: return 4697265;
            case 99: return 4987265;
            case 100: return 5250000;
            default: return 999999999;
        }
    }

    /**
     * Awards XP to a pet and handles leveling up.
     * $user_level is the current player level (cap).
     */
    public function awardPetXp(Pet $pet, int $char_xp_gain, int $char_level): array
    {
        // Pet only gets 20% of character XP
        $pet_xp_gain = floor($char_xp_gain * 0.2);
        
        $new_xp    = (int) $pet->pet_xp + $pet_xp_gain;
        $new_level = (int) $pet->pet_level;
        $level_up  = false;

        $xp_needed = $this->getPetExpNeededForLevel($new_level);

        while ($new_xp >= $xp_needed && $new_level < $char_level && $new_level < 100) {
            $new_xp -= $xp_needed;
            $new_level++;
            $level_up = true;
            $xp_needed = $this->getPetExpNeededForLevel($new_level);
        }

        // Apply cap: if pet level is already char_level, cap the XP so it doesn't spill over
        if ($new_level >= $char_level) {
            $new_level = min(100, $char_level);
            // Limit XP pool if capped
            $cap_xp = $this->getPetExpNeededForLevel($new_level) - 1;
            if ($new_xp > $cap_xp) {
               $new_xp = $cap_xp;
            }
        }

        $pet->pet_xp = $new_xp;
        $pet->pet_level = $new_level;
        $pet->save();

        return [
            'pet_level'    => $new_level,
            'pet_xp'       => $new_xp,
            'pet_level_up' => $level_up,
            'pet_xp_gain'  => $pet_xp_gain,
        ];
    }
}
