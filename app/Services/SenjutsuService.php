<?php

namespace App\Services;

class SenjutsuService
{
    use \App\Traits\SessionValidator;

    /**
     * getSenjutsuSkills
     * Client reads:
     *   param1.character_senjutsu_skills → stored to Character.character_senjutsu_skills
     *   param1.ss_points                 → senjutsu points
     *   param1.data                      → array of learned senjutsu skills
     *   param1.status == 1               → success path
     */
    public function getSenjutsuSkills($char_id, $sessionkey, $full = false): array
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof \App\Models\Character)) return $char;

        return [
            'status'                    => 1,
            'error'                     => 0,
            'data'                      => [],
            'ss_points'                 => $char->ss_points ?? 0,
            'character_senjutsu_skills' => $char->char_senjutsu_skills ?? '',
        ];
    }

    /**
     * discoverSenjutsu, upgradeSkill, reset, saveSet, useSenjutsuPointPill — stubs
     */
    public function discoverSenjutsu($char_id, $sessionkey, $type): array
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof \App\Models\Character)) return $char;

        if ($char->gold < 2000000) {
            return ['status' => 2, 'result' => 'Not enough Gold to discover Senjutsu.'];
        }

        $skill_a = ($type === 'toad') ? 'skill_3000' : 'skill_3100';
        $skill_b = ($type === 'toad') ? 'skill_3001' : 'skill_3101';

        $skills = $char->getInventoryArray('char_senjutsu_skills');
        if (isset($skills[$skill_a]) || isset($skills[$skill_b])) {
            return ['status' => 2, 'result' => 'Already discovered.'];
        }

        $char->gold -= 2000000;
        
        $skills[$skill_a] = 1;
        $skills[$skill_b] = 0; // The client expects at least level 0 to show the tree
        
        $char->setInventoryArray('char_senjutsu_skills', $skills);
        $char->save();

        return [
            'status' => 1,
            'golds'  => $char->gold,
            'tokens' => $char->user->tokens ?? 0,
        ];
    }

    public function upgradeSkill($char_id, $sessionkey, $skill_id): array
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof \App\Models\Character)) return $char;

        $skills = $char->getInventoryArray('char_senjutsu_skills');
        $current_level = $skills[$skill_id] ?? 0;
        $next_level = $current_level + 1;

        if ($next_level > 10) {
            return ['status' => 2, 'result' => 'Skill is already at max level.'];
        }

        $ss_costs = [
            1 => 5,
            2 => 10,
            3 => 25,
            4 => 50,
            5 => 100,
            6 => 200,
            7 => 300,
            8 => 450,
            9 => 600,
            10 => 800
        ];

        $cost = $ss_costs[$next_level];

        if ($char->ss_points < $cost) {
            return ['status' => 2, 'result' => "Not enough SS points."];
        }

        $char->ss_points -= $cost;
        $skills[$skill_id] = $next_level;
        $char->setInventoryArray('char_senjutsu_skills', $skills);
        $char->save();

        return ['status' => 1];
    }

    public function reset($char_id, $sessionkey): array
    {
        return ['status' => 1];
    }

    public function saveSet($char_id, $sessionkey, $skills): array
    {
        return ['status' => 1];
    }

    public function useSenjutsuPointPill($char_id, $sessionkey, $item_id, $qty): array
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof \App\Models\Character)) return $char;

        $qty = (int)$qty;
        if (!$char->hasInInventory('char_essentials', $item_id, $qty)) {
            return ['status' => 0, 'result' => 'You do not have enough items!'];
        }

        $ss_values = [
            'essential_122' => 10,
            'essential_123' => 50,
            'essential_124' => 100,
            'essential_201' => 10,
            'essential_202' => 50,
            'essential_203' => 100,
        ];

        $ss_per_pill = $ss_values[$item_id] ?? 10;
        $total_ss = $ss_per_pill * $qty;

        $char->ss_points = ($char->ss_points ?? 0) + $total_ss;
        $char->removeFromInventory('char_essentials', $item_id, $qty);
        $char->save();

        return [
            'status'         => 1,
            'essential_used' => $item_id,
            'total'          => $qty,
            'ss_got'         => $total_ss,
            'result'         => "Successfully used {$qty} pills and got {$total_ss} SS points!"
        ];
    }
}
