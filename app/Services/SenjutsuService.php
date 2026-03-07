<?php

namespace App\Services;

class SenjutsuService
{
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
        return [
            'status'                    => 1,
            'error'                     => 0,
            'data'                      => [],
            'ss_points'                 => 0,
            'character_senjutsu_skills' => '',
        ];
    }

    /**
     * discoverSenjutsu, upgradeSkill, reset, saveSet, useSenjutsuPointPill — stubs
     */
    public function discoverSenjutsu($char_id, $sessionkey, $type): array
    {
        return ['status' => 2, 'result' => 'Senjutsu discovery is not yet available.'];
    }

    public function upgradeSkill($char_id, $sessionkey, $skill_id): array
    {
        return ['status' => 2, 'result' => 'Senjutsu upgrades are not yet available.'];
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
        return ['status' => 2, 'result' => 'Not available.'];
    }
}
