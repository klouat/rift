<?php

namespace App\Services;

use App\Models\Character;
use App\Models\ValentineEvent2026;
use Illuminate\Support\Facades\Log;

class ValentineEvent2026Service
{
    use \App\Traits\SessionValidator;
    use \App\Traits\LevelManager;
    /**
     * executeService — Generic wrapper for ValentineEvent2026 calls.
     */
    public function executeService($action, $params = [])
    {
        switch ($action) {
            case 'getData':
                return $this->getData($params[0] ?? 0);
            case 'claimFreeGift':
                return $this->claimFreeGift($params[0] ?? 0);
            case 'startBattle':
                return $this->startBattle($params[0] ?? 0, $params[2] ?? 0);
            case 'endBattle':
                return $this->endBattle($params[0] ?? 0);
            case 'claimBattleProgress':
                return $this->claimBattleProgress($params[0] ?? 0, $params[2] ?? 0, $params[3] ?? 0);
            case 'claimGachaProgress':
                return $this->claimGachaProgress($params[0] ?? 0, $params[2] ?? 0);
            case 'claimTaskReward':
                return $this->claimTaskReward($params[0] ?? 0, $params[2] ?? 0);
            case 'buyPackage':
                return $this->buyPackage($params[0] ?? 0, $params[2] ?? 0);
            case 'buySkillPackage':
                return $this->buySkillPackage($params[0] ?? 0, $params[2] ?? 0);
            case 'getGachaHistory':

                return $this->getGachaHistory();
            default:
                Log::warning("ValentineEvent2026: Unknown action '{$action}'", ['params' => $params]);
                return ['status' => 1];
        }
    }

    /**
     * getGachaRewards — Gacha spin.
     * Note: This is a direct method call from ActionScript, not via executeService.
     */
    public function getGachaRewards($sessionkey, $char_id, $type, $amount)
    {
        $char = Character::find((int) $char_id);
        if (!$char) return ['status' => 0, 'result' => 'Character not found'];

        $event = $this->getEventData($char_id);

        $cost = (int)$amount * 20;
        if ($type === 'tokens') {
            $user = $char->user;
            if ($user->tokens < $cost) return ['status' => 0, 'result' => 'Not enough tokens'];
            $user->tokens -= $cost;
            $user->save();
        } else {
            // Material material_296 (tickets)
            if (!$char->removeFromInventory('char_materials', 'material_296', (int)$amount)) {
                return ['status' => 0, 'result' => 'Not enough tickets'];
            }
        }

        $rewards = [];
        $pool = $this->getGachaPool();
        for ($i = 0; $i < (int)$amount; $i++) {
            $reward_id = array_rand($pool);
            $rewards[] = [$pool[$reward_id]];
        }

        $event->total_draws += (int)$amount;
        $event->save();

        return [
            'status' => 1,
            'rewards' => $rewards
        ];
    }

    private function getData($char_id): array
    {
        $event = $this->getEventData($char_id);
        $this->updateEnergy($event);

        return [
            'status' => 1,
            'menuData' => [
                'id' => 3369,
                'char_id' => (int)$char_id,
                'canClaimFreeGift' => $event->can_claim_free_gift ? 1 : 0,
                'rewards' => ['essential_141', 'essential_142', 'essential_143', 'essential_144']
            ],
            'battleData' => [
                'id' => 3369,
                'char_id' => (int)$char_id,
                'energy' => $event->energy,
                'max_energy' => $event->max_energy,
                'total_battles' => array_sum($event->battle_kills),
                'enemies' => [
                    ['ene_399'],
                    ['ene_402', 'ene_401'],
                    ['ene_400']
                ],
                'killsRequired' => [10, 25, 50, 75, 100, 125, 150, 175, 200],
                'progressRewards' => $this->getBattleProgressRewards(),
                'battleProgress' => $this->getBattleProgress($event),
                'battleProgressData' => $this->getBattleProgressData($event)
            ],
            'gachaData' => [
                'id' => 3369,
                'char_id' => (int)$char_id,
                'total_draws' => $event->total_draws,
                'gacha_material' => 'material_296',
                'progressRewards' => ['essential_141', 'essential_142', 'essential_143', 'essential_144', 'essential_98_1', 'essential_99_1', 'set_1283_0', 'wpn_1469', 'skill_1447'],
                'killsRequired' => [10, 50, 100, 200, 300, 400, 500, 600, 750],
                'rewards' => $this->getGachaRewardsList(),
            ],
            'gachaProgressData' => $this->getGachaProgressData($event),
            'trainingData' => [
                'id' => 3369,
                'char_id' => (int)$char_id,
                'pack_0' => $event->pack_0,
                'pack_1' => $event->pack_1,
                'pack_2' => $event->pack_2,
                'skills' => ['skill_1443', 'skill_1444', 'skill_1445'],
                'prices' => [8000, 8000, 8000]
            ],

            'tasksData' => $event->tasks_status,
            'dealsData' => [
                'id' => 3369,
                'char_id' => (int)$char_id,
                'pack_0' => $event->deal_0,
                'pack_1' => $event->deal_1,
                'pack_2' => $event->deal_2,
                'pack_3' => $event->deal_3,
                'rewards' => ['essential_25_10', 'essential_24_20', 'essential_36_30', 'essential_141_3', 'essential_142_3', 'essential_143_3', 'essential_144_3', 'essential_151', 'essential_152', 'essential_125_3', 'essential_08_3'],
                'extra_data' => false
            ],
            'extra_data' => false
        ];
    }

    private function claimFreeGift($char_id): array
    {
        $event = $this->getEventData($char_id);
        if (!$event->can_claim_free_gift) return ['status' => 0, 'result' => 'Already claimed'];

        $event->can_claim_free_gift = false;
        $event->save();

        $char = Character::find((int)$char_id);
        $rewards = ['essential_141', 'essential_142', 'essential_143', 'essential_144'];
        foreach ($rewards as $r) $char->addToInventory('char_essentials', $r);
        $char->save();

        $data = $this->getData($char_id);
        $data['extra_data'] = ['rewards' => $rewards];
        return $data;
    }

    private function startBattle($char_id, $boss_idx): array
    {
        $event = $this->getEventData($char_id);
        if ($event->energy < 10) return ['status' => 0, 'result' => 'Low Energy'];

        $event->energy -= 10;
        $event->pending_boss_idx = (int)$boss_idx;
        $event->save();


        // Register that the character is in this event fight for endBattle logic
        // For now just return the battle code
        $enemy_groups = [
            ['ene_399'],
            ['ene_402', 'ene_401'],
            ['ene_400']
        ];
        $enemies = $enemy_groups[(int)$boss_idx] ?? ['ene_399'];

        return [
            'status' => 1,
            'battle_code' => substr(md5(uniqid($char_id . $boss_idx, true)), 0, 10),
            'enemy_id' => $enemies,
            'missionBackground' => 'mission_valentines2026',
            'capture_range_start' => rand(1, 4),
            'capture_range_end' => rand(6, 12),
            'reduce_gold' => 0,
            'reduce_token' => 0
        ];
    }


    private function endBattle($char_id): array
    {
        $event = $this->getEventData($char_id);
        if ($event->pending_boss_idx !== null) {
            $kills = $event->battle_kills;
            $kills[(int)$event->pending_boss_idx]++;
            $event->battle_kills = $kills;
            $event->pending_boss_idx = null;
            $event->save();
        }

        $char = Character::find((int)$char_id);
        
        $xp_gain = 0; // Events currently don't award XP on endBattle, change here if needed.
        $awards = $this->awardXp($char, $xp_gain);
        
        $char->xp = $awards['xp'];
        $char->level = $awards['level'];
        $char->save();

        return [
            'status' => 1,
            'xp' => $awards['xp'],
            'level' => $awards['level'],
            'level_up' => $awards['level_up'],
            'result' => ['' . $xp_gain, '0', []]
        ];
    }


    private function claimBattleProgress($char_id, $boss_idx, $reward_idx): array
    {
        $event = $this->getEventData($char_id);
        $kills = $event->battle_kills[(int)$boss_idx] ?? 0;
        $req = [10, 25, 50, 75, 100, 125, 150, 175, 200];
        
        if ($kills < ($req[(int)$reward_idx] ?? 999)) return ['status' => 0, 'result' => 'Not enough kills'];

        $claims = $event->battle_claims;
        if (in_array((int)$reward_idx, $claims[(int)$boss_idx])) return ['status' => 0, 'result' => 'Already claimed'];

        $claims[(int)$boss_idx][] = (int)$reward_idx;
        $event->battle_claims = $claims;
        $event->save();

        $char = Character::find((int)$char_id);
        $rewards = $this->getBattleProgressRewards()[(int)$boss_idx];
        $reward = $rewards[(int)$reward_idx] ?? null;

        if ($reward) {
            $this->awardItem($char, $reward);
        }

        $data = $this->getData($char_id);
        if ($reward) {
            $data['extra_data'] = ['rewards' => [$reward]];
        }
        return $data;
    }

    private function awardItem(Character $char, string $item): void
    {
        if (str_starts_with($item, 'essential_')) {
            $p = explode('_', $item);
            if (count($p) == 3) {
                $char->addToInventory('char_essentials', "essential_{$p[1]}", (int)$p[2]);
            } else {
                $char->addToInventory('char_essentials', $item, 1);
            }
        } elseif (str_starts_with($item, 'material_')) {
            $p = explode('_', $item);
            if (count($p) == 3) {
                $char->addToInventory('char_materials', "material_{$p[1]}", (int)$p[2]);
            } else {
                $char->addToInventory('char_materials', $item, 1);
            }
        } elseif (str_starts_with($item, 'skill_')) {
            $char->addToInventory('char_skills', $item);
        } elseif (str_starts_with($item, 'wpn_')) {
            $char->addToInventory('char_weapons', $item);
        } elseif (str_starts_with($item, 'hair_')) {
            $char->addToInventory('char_hairs', $item);
        } elseif (str_starts_with($item, 'set_')) {
            $char->addToInventory('char_sets', $item);
        } elseif (str_starts_with($item, 'back_')) {
            $char->addToInventory('char_back_items', $item);
        } elseif (str_starts_with($item, 'accessory_')) {
            $char->addToInventory('char_accessories', $item);
        } elseif (str_starts_with($item, 'gold_')) {
            $p = explode('_', $item);
            $amount = (int)end($p);
            $char->gold += $amount;
        } elseif (str_starts_with($item, 'tokens_')) {
            $p = explode('_', $item);
            $amount = (int)end($p);
            $user = $char->user;
            if ($user) {
                $user->tokens += $amount;
                $user->save();
            }
        }
        $char->save();
    }

    private function claimGachaProgress($char_id, $reward_idx): array
    {
        $event = $this->getEventData($char_id);
        if ($event->total_draws < ([10, 50, 100, 200, 300, 400, 500, 600, 750][(int)$reward_idx] ?? 999)) {
            return ['status' => 0, 'result' => 'Not enough draws'];
        }

        $claims = $event->gacha_claims;
        if (in_array((int)$reward_idx, $claims)) return ['status' => 0, 'result' => 'Already claimed'];

        $claims[] = (int)$reward_idx;
        $event->gacha_claims = $claims;
        $event->save();

        return $this->getData($char_id);
    }

    private function claimTaskReward($char_id, $task_id): array
    {
        $event = $this->getEventData($char_id);
        $tasks = $event->tasks_status;
        $found = false;
        foreach ($tasks as &$t) {
            if ($t['task_id'] == $task_id && $t['claimed'] == 0) {
                // Check if total requirement met. 
                // In a real system we'd check against actual task progress.
                if ($t['total'] >= 1) { 
                    $t['claimed'] = 1;
                    $found = true;
                    // grant $t['task_reward']
                }
            }
        }
        if (!$found) return ['status' => 0, 'result' => 'Cannot claim task'];

        $event->tasks_status = $tasks;
        $event->save();
        return $this->getData($char_id);
    }

    private function buyPackage($char_id, $pack_idx): array
    {
        $char = Character::find((int)$char_id);
        $event = $this->getEventData($char_id);
        
        $prices = [799, 799, 1599, 499];
        $price = $prices[(int)$pack_idx] ?? 9999;

        $user = $char->user;
        if ($user->tokens < $price) return ['status' => 0, 'result' => 'Not enough tokens'];

        $user->tokens -= $price;
        $user->save();

        $event->{"deal_" . (int)$pack_idx} = 1;
        $event->save();

        $rewards = ['essential_25_10', 'essential_24_20', 'essential_36_30']; // Simplified
        foreach ($rewards as $r) $char->addToInventory('char_essentials', $r);
        $char->save();

        $data = $this->getData($char_id);
        $data['rewards'] = $rewards; // Deals.as expects 'rewards' and 'status'
        return $data;
    }

    private function buySkillPackage($char_id, $buy_id): array
    {
        $char = Character::find((int)$char_id);
        $event = $this->getEventData($char_id);

        $prices = [2999, 4999, 7999]; // Example prices based on AS (multiplied by something or fixed)
        $price = $prices[(int)$buy_id] ?? 9999;
        if ($char->user->account_type > 0) $price = floor($price / 2) - 1;

        if ($char->user->tokens < $price) return ['status' => 0, 'result' => 'Not enough tokens'];

        $char->user->tokens -= $price;
        $char->user->save();

        $event->{"pack_" . (int)$buy_id} = 1;
        $event->save();

        // Specific skill grants
        $skills_map = ['skill_1443', 'skill_1444', 'skill_1445'];
        $skill = $skills_map[(int)$buy_id] ?? null;
        if ($skill) {
            $char->addToInventory('char_skills', $skill);
            $char->save();
        }

        $data = $this->getData($char_id);
        $data['extra_data'] = [
            'message' => 'Skills updated!',
            'reduce_tokens' => $price, // We already reduced them in the user model
            'reward' => $skill ? [$skill] : [],
            'remove_skill' => '',
            'data_skill' => 'false'
        ];
        return $data;
    }

    private function getEventData($char_id): ValentineEvent2026

    {
        $event = ValentineEvent2026::where('char_id', (int)$char_id)->first();
        if (!$event) {
            $event = ValentineEvent2026::create([
                'char_id' => (int)$char_id,
                'energy' => 100,
                'max_energy' => 100,
                'last_energy_refill' => now(),
                'battle_kills' => [0, 0, 0],
                'battle_claims' => [[], [], []],
                'gacha_claims' => [],
                'tasks_status' => $this->getDefaultTasks((int)$char_id)
            ]);
        }
        return $event;
    }

    private function updateEnergy(ValentineEvent2026 $event)
    {
        $now = now();
        $diff = $now->diffInMinutes($event->last_energy_refill);
        if ($diff >= 5 && $event->energy < $event->max_energy) {
            $gain = floor($diff / 5);
            $event->energy = min($event->max_energy, $event->energy + $gain);
            $event->last_energy_refill = $event->last_energy_refill->addMinutes($gain * 5);
            $event->save();
        }
    }

    private function getBattleProgress($event): array
    {
        return [
            ['id' => 10105, 'char_id' => (int)$event->char_id, 'boss_id' => 0, 'total_kills' => $event->battle_kills[0]],
            ['id' => 10106, 'char_id' => (int)$event->char_id, 'boss_id' => 1, 'total_kills' => $event->battle_kills[1]],
            ['id' => 10107, 'char_id' => (int)$event->char_id, 'boss_id' => 2, 'total_kills' => $event->battle_kills[2]],
        ];
    }

    private function getBattleProgressData($event): array
    {
        $data = [[], [], []];
        for ($b = 0; $b < 3; $b++) {
            for ($r = 0; $r < 9; $r++) {
                $data[$b][$r] = ['claimed' => in_array($r, $event->battle_claims[$b]) ? 1 : 0];
            }
        }
        return $data;
    }

    private function getGachaProgressData($event): array
    {
        $data = [];
        for ($r = 0; $r < 9; $r++) {
            $data[] = [
                'id' => 30313 + $r,
                'char_id' => (int)$event->char_id,
                'reward_id' => $r,
                'claimed' => in_array($r, $event->gacha_claims) ? 1 : 0
            ];
        }
        return $data;
    }

    private function getDefaultTasks($char_id): array
    {
        return [
            ['id' => 80833, 'char_id' => (int)$char_id, 'task_id' => 1, 'total' => 1, 'claimed' => 0, 'task_details' => 'Log in Daily for [total]/1 Day', 'task_reward' => 'essential_61']
        ];
    }

    private function getBattleProgressRewards(): array
    {
        $shared = ['essential_52_5', 'essential_105_5', 'essential_141', 'essential_142', 'essential_143', 'essential_144', 'material_66_10', 'essential_98', 'essential_99'];
        return [$shared, $shared, $shared];
    }

    private function getGachaRewardsList(): array
    {
        return ["tokens_3000", "essential_07", "wpn_1467", "pet_rose_dragon", "skill_1446", "skill_1448", "skill_1449", "skill_318", "skill_903", "skill_1296", "skill_1297", "skill_1298", "skill_959", "skill_960", "skill_436", "skill_435", "skill_434", "skill_426", "skill_324", "skill_319", "skill_329", "skill_328", "skill_974", "pet_valentine_kitty", "pet_devil_angel", "pet_heart_monster", "pet_love_fairy", "pet_red_panda", "wpn_1468", "wpn_1470", "wpn_1471", "wpn_1472", "wpn_1473", "wpn_1344", "wpn_1345", "wpn_1346", "wpn_1347", "wpn_1356", "wpn_1355", "wpn_1354", "wpn_796", "wpn_797", "back_821", "back_681", "back_682", "back_683", "back_684", "back_685", "back_443", "back_444", "back_445", "back_446", "back_447", "back_448", "back_342", "back_340", "set_1278_0", "set_1279_0", "set_1280_0", "set_1281_0", "set_1282_0", "set_788_0", "set_1149_0", "set_1066_0", "set_1067_0", "set_720_0", "set_724_0", "set_700_0", "set_587_0", "set_597_0", "hair_66_0", "hair_79_0", "hair_450_0", "hair_437_0", "hair_438_0", "hair_439_0", "hair_68_0", "essential_24", "essential_105", "essential_52", "essential_12", "essential_49", "essential_41", "gold_"];
    }

    private function getGachaPool(): array
    {
        // Simple equal chance pool for now
        return $this->getGachaRewardsList();
    }

    private function getGachaHistory(): array
    {
        return [
            'status' => 1,
            'total_won' => 0,
            'result' => []
        ];
    }
}
