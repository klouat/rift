<?php

namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Facades\Log;

class LevelUpPackagesService
{
    use \App\Traits\LevelManager;
    use \App\Traits\RewardHandler;
    /**
     * executeService — Generic wrapper for LevelUpPackages calls.
     */
    public function executeService($action, $params = [])
    {
        switch ($action) {
            case 'getRewards':
                return $this->getRewards();
            case 'getPacks':
                return $this->getPacks($params[0] ?? 0);
            case 'startPurchase':
                return $this->startPurchase($params[0] ?? 0, $params[2] ?? 0);
            default:
                Log::warning("LevelUpPackages: Unknown action '{$action}'", ['params' => $params]);
                return ['status' => 1];
        }
    }

    /**
     * Returns the 10 packages of rewards.
     */
    private function getRewards(): array
    {
        return [
            // Level 10
            ["skill_902", "wpn_526", "back_452", "essential_52_15", "set_231", "hair_108", "face_01", "tokens_700", "essential_57_5", "pet_mini_taiko"],
            // Level 20
            ["skill_919", "wpn_990", "back_190", "accessory_119", "set_644", "hair_31", "face_01", "tokens_2000", "essential_96_5", "pet_tanuki"],
            // Level 30
            ["skill_412", "wpn_345", "back_73", "essential_57_5", "set_153", "hair_78", "face_01", "tokens_2500", "essential_97_5", "pet_panda"],
            // Level 40
            ["skill_343", "wpn_571", "back_467", "essential_08_10", "set_645", "hair_283", "face_01", "tokens_3000", "essential_96_5", "pet_ghost_lights"],
            // Level 50
            ["skill_316", "wpn_631", "back_657", "essential_53_3", "set_261", "hair_158", "face_01", "tokens_4100", "essential_107_100", "pet_silver_monkey_king"],
            // Level 60
            ["skill_330", "wpn_696", "back_595", "essential_103_20", "set_619", "hair_271", "face_01", "tokens_5200", "essential_55_15", "pet_falcon"],
            // Level 70
            ["skill_407", "wpn_686", "back_359", "essential_56_2", "set_646", "hair_253", "face_01", "tokens_8000", "essential_106_50", "pet_nibi"],
            // Level 80
            ["skill_376", "wpn_396", "back_154", "essential_98_10", "set_215", "hair_126", "face_01", "tokens_15000", "essential_99_10", "pet_christmas_kyubi"],
            // Level 90
            ["skill_1235", "wpn_899", "back_492", "accessory_126", "set_719", "hair_330", "face_01", "tokens_20000", "essential_55_20", "pet_ultimate_kirin_iv"],
            // Level 100
            ["skill_1275", "wpn_838", "back_455", "accessory_162", "set_729", "hair_336", "face_01", "tokens_30000", "essential_55_25", "pet_tornado_jyubi"]
        ];
    }

    /**
     * getPacks — Returns claim status of all 10 packages.
     */
    private function getPacks($char_id): array
    {
        $char = Character::find((int) $char_id);
        
        $packs = array_fill(0, 10, 0);
        
        if ($char && $char->level_up_packages) {
            $parts = explode(',', $char->level_up_packages);
            foreach ($parts as $i => $v) {
                if (isset($packs[$i])) {
                    $packs[$i] = (int)$v;
                }
            }
        }

        return [
            'status' => 1,
            'data'   => $packs
        ];
    }

    /**
     * startPurchase — simulate purchase of a level up package.
     */
    private function startPurchase($char_id, $pack_num): array
    {
        $char = Character::find((int) $char_id);
        if (!$char) return ['status' => 0, 'result' => 'Character not found'];

        // In an offline backend, we mark as claimed immediately 
        // effectively bypassing PlayStore integration.
        $pack_idx = (int)$pack_num - 1;
        if ($pack_idx < 0 || $pack_idx > 9) {
            return ['status' => 0, 'result' => 'Invalid package index'];
        }

        $this->rewardPackage($char, $pack_idx);
        
        return [
            'status' => 1,
            'result' => 'Purchase successful (Offline mode).'
        ];
    }

    private function rewardPackage($char, $pack_idx): void
    {
        // Mark as claimed
        $packs = array_fill(0, 10, 0);
        if ($char->level_up_packages) {
            $parts = explode(',', $char->level_up_packages);
            foreach ($parts as $i => $v) if (isset($packs[$i])) $packs[$i] = (int)$v;
        }
        $packs[$pack_idx] = 1;
        $char->level_up_packages = implode(',', $packs);

        // Giveaway rewards
        $all_rewards = $this->getRewards();
        $rewards = $all_rewards[$pack_idx] ?? [];

        $granted = [];
        foreach ($rewards as $item) {
            $this->processSingleReward($char, $item, $granted);
        }

        $char->save();
    }
}
