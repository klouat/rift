<?php

namespace App\Services;

use App\Models\Character;
use App\Models\HanamiEvent2026;
use Illuminate\Support\Facades\Log;

class HanamiEvent2026Service
{
    use \App\Traits\SessionValidator;
    use \App\Traits\LevelManager;

    /**
     * executeService — Generic wrapper for HanamiEvent2026 calls.
     */
    public function executeService($action, $params = [])
    {
        switch ($action) {
            case 'getData':
                return $this->getData($params[0] ?? 0, $params[1] ?? "");
            case 'claimFreeGift':
                return $this->claimFreeGift($params[0] ?? 0, $params[1] ?? "");
            case 'startBattle':
                return $this->startBattle($params[0] ?? 0, $params[1] ?? "", $params[2] ?? 0);
            case 'endBattle':
                return $this->endBattle($params[0] ?? 0, $params[1] ?? "");
            case 'claimBattleProgress':
                return $this->claimBattleProgress($params[0] ?? 0, $params[1] ?? "", $params[2] ?? 0, $params[3] ?? 0);
            case 'claimGachaProgress':
                return $this->claimGachaProgress($params[0] ?? 0, $params[1] ?? "", $params[2] ?? 0);
            case 'claimTaskReward':
                return $this->claimTaskReward($params[0] ?? 0, $params[1] ?? "", $params[2] ?? 0);
            case 'buyPackage':
                return $this->buyPackage($params[0] ?? 0, $params[1] ?? "", $params[2] ?? 0);
            case 'buySkillPackage':
                return $this->buySkillPackage($params[0] ?? 0, $params[1] ?? "", $params[2] ?? 0);
            case 'getGachaHistory':
                return $this->getGachaHistory($params[0] ?? 0, $params[1] ?? "");
            default:
                Log::warning("HanamiEvent2026: Unknown action '{$action}'", ['params' => $params]);
                return ['status' => 1];
        }
    }

    /**
     * getGachaRewards — Hanami direct gacha spin.
     */
    public function getGachaRewards($sessionkey, $char_id, $type, $amount)
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        $event = $this->getEventData($char_id);

        $cost = (int)$amount * 20; 
        if ($type === 'tokens') {
            $user = $char->user;
            if ($user->tokens < $cost) return ['status' => 0, 'result' => 'Not enough tokens'];
            $user->tokens -= $cost;
            $user->save();
        } else {
            // Material material_346 (hanami tickets)
            if (!$char->removeFromInventory('char_materials', 'material_346', (int)$amount)) {
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
                'id' => 2450,
                'char_id' => (int)$char_id,
                'canClaimFreeGift' => $event->can_claim_free_gift ? 1 : 0,
                'rewards' => ['essential_145', 'essential_146', 'essential_147', 'essential_148']
            ],
            'battleData' => [
                'id' => 2450,
                'char_id' => (int)$char_id,
                'energy' => $event->energy,
                'max_energy' => $event->max_energy,
                'total_battles' => array_sum($event->battle_kills),
                'enemies' => [
                    ['ene_290'], ['ene_289'], ['ene_111'], ['ene_112'], ['ene_393'], ['ene_343']
                ],
                'killsRequired' => [10, 25, 50, 75, 100, 125, 150, 175, 200],
                'progressRewards' => $this->getBattleProgressRewards(),
                'battleProgress' => $this->getBattleProgress($event),
                'battleProgressData' => $this->getBattleProgressData($event)
            ],
            'gachaData' => [
                'id' => 2450,
                'char_id' => (int)$char_id,
                'total_draws' => $event->total_draws,
                'gacha_material' => 'material_346',
                'progressRewards' => [],
                'killsRequired' => [],
                'rewards' => [],
            ],
            'gachaProgressData' => [],
            'trainingData' => [
                'id' => 2450,
                'char_id' => (int)$char_id,
                'pack_0' => $event->pack_0,
                'skills' => ['skill_1450'],
                'prices' => [8000]
            ],
            'tasksData' => $event->tasks_status,
            'dealsData' => [
                'id' => 2450,
                'char_id' => (int)$char_id,
                'pack_0' => $event->deal_0,
                'pack_1' => $event->deal_1,
                'pack_2' => $event->deal_2,
                'pack_3' => $event->deal_3,
                'pack_4' => $event->deal_4,
                'pack_5' => $event->deal_5,
                'rewards' => ['essential_25_10', 'essential_24_20', 'essential_36_30', 'essential_125_3', 'essential_08_3', 'essential_145_3', 'essential_146_3', 'essential_147_3', 'essential_148_3', 'essential_106_20', 'essential_41_50', 'essential_19_20', 'essential_119_2', 'essential_151', 'essential_152', 'wpn_722'],
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
        $rewards = ['essential_145', 'essential_146', 'essential_147', 'essential_148'];
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

        $enemy_groups = [
            ['ene_290'], ['ene_289'], ['ene_111'], ['ene_112'], ['ene_393'], ['ene_343']
        ];
        $enemies = $enemy_groups[(int)$boss_idx] ?? ['ene_290'];

        return [
            'status' => 1,
            'battle_code' => substr(md5(uniqid($char_id . $boss_idx, true)), 0, 10),
            'enemy_id' => $enemies,
            'missionBackground' => 'mission_76',
            'capture_range_start' => rand(1, 3),
            'capture_range_end' => rand(5, 9),
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
        
        $xp_gain = 0; // Hanami currently doesn't award XP on endBattle
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
        return $this->getData($char_id);
    }

    private function claimTaskReward($char_id, $task_id): array
    {
        return $this->getData($char_id);
    }

    private function buyPackage($char_id, $pack_idx): array
    {
        $char = Character::find((int)$char_id);
        $event = $this->getEventData($char_id);
        $event->{"deal_" . (int)$pack_idx} = 1;
        $event->save();

        return $this->getData($char_id);
    }

    private function buySkillPackage($char_id, $buy_id): array
    {
        $char = Character::find((int)$char_id);
        $event = $this->getEventData($char_id);

        $price = 8000;
        if ($char->user->account_type > 0) $price = 3999; 

        if ($char->user->tokens < $price) return ['status' => 0, 'result' => 'Not enough tokens'];

        $char->user->tokens -= $price;
        $char->user->save();

        $event->pack_0 = 1;
        $event->save();

        $skill = 'skill_1450';
        $char->addToInventory('char_skills', $skill);
        $char->save();

        $data = $this->getData($char_id);
        $data['extra_data'] = [
            'message' => 'Skills updated!',
            'reduce_tokens' => $price,
            'reward' => [$skill],
            'remove_skill' => '',
            'data_skill' => 'false'
        ];
        return $data;
    }


    private function getEventData($char_id): HanamiEvent2026
    {
        $event = HanamiEvent2026::where('char_id', (int)$char_id)->first();
        if (!$event) {
            $event = HanamiEvent2026::create([
                'char_id' => (int)$char_id,
                'energy' => 100,
                'max_energy' => 100,
                'last_energy_refill' => now(),
                'battle_kills' => array_fill(0, 6, 0),
                'battle_claims' => array_fill(0, 6, []),
                'gacha_claims' => [],
                'tasks_status' => []
            ]);
        }
        return $event;
    }

    private function updateEnergy(HanamiEvent2026 $event)
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
        $res = [];
        for ($i = 0; $i < 6; $i++) {
            $res[] = ['id' => 14695 + $i, 'char_id' => (int)$event->char_id, 'boss_id' => $i, 'total_kills' => $event->battle_kills[$i]];
        }
        return $res;
    }

    private function getBattleProgressData($event): array
    {
        $data = [];
        for ($b = 0; $b < 6; $b++) {
            $bossClaims = [];
            for ($r = 0; $r < 9; $r++) {
                $bossClaims[] = ['id' => 132247 + ($b*9) + $r, 'char_id' => (int)$event->char_id, 'boss_id' => $b, 'reward_id' => $r, 'claimed' => in_array($r, $event->battle_claims[$b]) ? 1 : 0];
            }
            $data[] = $bossClaims;
        }
        return $data;
    }

    private function getBattleProgressRewards(): array
    {
        $shared = ['essential_52_5', 'essential_105_5', 'essential_145', 'essential_146', 'essential_147', 'essential_148', 'material_66_10', 'essential_98', 'essential_99'];
        return array_fill(0, 6, $shared);
    }

    private function getGachaPool(): array
    {
        return ["gold_1000"]; // Placeholder
    }

    private function getGachaHistory(): array
    {
        return ['status' => 1, 'total_won' => 0, 'result' => []];
    }
}
