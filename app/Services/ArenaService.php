<?php

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterArena;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class ArenaService {
    use \App\Traits\SessionValidator;

    public function executeService($method, $params) {
        if (!method_exists($this, $method)) {
            Log::warning("ArenaService: Unhandled method {$method}", ['params' => $params]);
            return ["status" => 1];
        }
        return $this->$method(...$params);
    }

    public function getData($char_id, $sessionkey) {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        $arena = $char->arena ?: CharacterArena::create(['char_id' => $char->id]);
        
        $season = (int) SystemSetting::get('arena_season', 1);
        $endTime = SystemSetting::get('arena_end_time');
        
        // Calculate remaining seconds
        $timestamp = 0;
        if ($endTime) {
            $remaining = strtotime($endTime) - time();
            $timestamp = max(0, $remaining);
        }

        return [
            "status" => 1,
            "season" => $season,
            "data"   => [
                "village_changed" => (int) $arena->village_changed,
                "first_open"      => (int) $arena->first_open,
            ],
            "can_change_village" => true,
            "all_rewards" => [
                "tokens_100", "gold_50000", "essential_01_5", "material_05_10",
                "set_01", "hair_01", "wpn_02", "back_01"
            ],
            "timestamp" => $timestamp
        ];
    }

    public function playedAnimation($char_id, $sessionkey) {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        $arena = $char->arena;
        if ($arena) {
            $arena->first_open = 0;
            $arena->save();
        }
        return ["status" => 1];
    }

    public function getRanking($char_id, $sessionkey, $type) {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        // Mock rankings based on total trophies in DB
        $characters = Character::with(['user', 'arena'])->get()->sortByDesc(fn($c) => $c->arena->trophies ?? 0)->take(20);
        
        $data = [];
        foreach($characters as $c) {
            $data[] = [
                "character_id"    => $c->id,
                "character_name"  => $c->name,
                "character_level" => $c->level,
                "village_id"      => (int) (($c->village_id > 0) ? $c->village_id : 1),
                "trophies"        => (int) ($c->arena->trophies ?? 0),
                "weapon"          => $c->equipped_weapon,
                "back_item"       => $c->equipped_back_item,
                "clothing"        => $c->equipped_clothing,
                "hairstyle"       => $c->equipped_hairstyle,
                "face"            => "face_01_" . ($c->gender ?? 1),
                "hair_color"      => $c->hair_style_color,
                "skin_color"      => $c->skin_color,
                "village_trophies" => 1000
            ];
        }

        // Pad with mock data if less than 10 entries to prevent ActionScript Error #1010
        // The client's showTopThree() likely expects at least 3 entries, and the list board often expects 10.
        $mock_names = ["Shinobi", "Shadow", "Kensei", "Ronin", "Ninja", "Warrior", "Seeker", "Ghost", "Specter", "Wraith"];
        while (count($data) < 10) {
            $i = count($data);
            $data[] = [
                "character_id"    => 1000 + $i,
                "character_name"  => $mock_names[$i % count($mock_names)],
                "character_level" => 100,
                "village_id"      => ($i % 5) + 1,
                "trophies"        => 1000 - ($i * 50),
                "weapon"          => "wpn_01",
                "back_item"       => "",
                "clothing"        => "set_01_1",
                "hairstyle"       => "hair_01_1",
                "face"            => "face_01_1",
                "hair_color"      => "0|0",
                "skin_color"      => 16173743,
                "village_trophies" => 1000
            ];
        }

        return ["status" => 1, "data" => $data];
    }

    public function getTrophyRewards($char_id, $sessionkey) {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        $arena = $char->arena;
        if (!$arena) return ["status" => 1, "data" => []];
        
        $claimed = $arena->claimed_trophy_rewards ?? [];

        $rewards = [
            ["trophies_needed" => 100,  "reward" => "tokens_50", "claimed" => in_array("rank_1", $claimed) ? 1 : 0, "id" => "rank_1"],
            ["trophies_needed" => 500,  "reward" => "gold_10000", "claimed" => in_array("rank_2", $claimed) ? 1 : 0, "id" => "rank_2"],
            ["trophies_needed" => 1000, "reward" => "tokens_150", "claimed" => in_array("rank_3", $claimed) ? 1 : 0, "id" => "rank_3"],
            ["trophies_needed" => 2000, "reward" => "wpn_05", "claimed" => in_array("rank_4", $claimed) ? 1 : 0, "id" => "rank_4"],
            ["trophies_needed" => 5000, "reward" => "tokens_500", "claimed" => in_array("rank_5", $claimed) ? 1 : 0, "id" => "rank_5"],
        ];

        return ["status" => 1, "data" => $rewards];
    }

    public function startBattle($char_id, $sessionkey) {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        $arena = $char->arena;
        if (!$arena || $arena->stamina < 10) {
            return ["status" => 2, "result" => "Not enough stamina."];
        }

        $arena->stamina -= 10;
        $arena->save();

        $battle_code = md5(time() . $char_id);
        
        return [
            "status" => 1,
            "battle_code" => $battle_code
        ];
    }

    public function endBattle($char_id, $sessionkey, $battle_code, $damage, $hash) {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        // Simple reward for winning
        $arena = $char->arena;
        $arena->trophies += 50;
        $arena->save();

        return [
            "status" => 1,
            "xp" => 100,
            "level" => $char->level,
            "trophies_got" => 50,
            "result" => [100, 500, [], ["tokens_5"]], // result[3] is rewards list
            "level_up" => false
        ];
    }

    public function claimTrophyRewards($char_id, $sessionkey) {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        $arena = $char->arena;
        $claimed = $arena->claimed_trophy_rewards ?? [];
        
        // Find first unclaimed reward that player qualifies for
        $rewards = [
            "rank_1" => ["needed" => 100,  "reward" => "tokens_50"],
            "rank_2" => ["needed" => 500,  "reward" => "gold_10000"],
            "rank_3" => ["needed" => 1000, "reward" => "tokens_150"],
            "rank_4" => ["needed" => 2000, "reward" => "wpn_05"],
            "rank_5" => ["needed" => 5000, "reward" => "tokens_500"],
        ];

        foreach($rewards as $id => $data) {
            if (!in_array($id, $claimed) && $arena->trophies >= $data['needed']) {
                $claimed[] = $id;
                $arena->claimed_trophy_rewards = $claimed;
                $arena->save();

                return [
                    "status" => 1,
                    "reward" => $data['reward']
                ];
            }
        }

        return ["status" => 0, "result" => "No reward to claim."];
    }

    public function changeEnemy($char_id, $sessionkey) {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        $arena = $char->arena;
        $enemy = Character::where('id', '!=', $char_id)->inRandomOrder()->first();
        if ($enemy) {
            $arena->enemy_id = $enemy->id;
            $arena->save();
        }

        // Return getData structure as gotData expects it
        return $this->getData($char_id, $sessionkey);
    }

    public function changeVillage($char_id, $sessionkey, $village_id) {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        $char->village_id = $village_id;
        $char->save();
        
        $arena = $char->arena;
        $arena->village_changed = 1;
        $arena->save();

        return $this->getData($char_id, $sessionkey);
    }

    public function restoreEnergy($char_id, $sessionkey, $random_str) {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        $arena = $char->arena;
        if (!$arena) return ["status" => 0];

        // Check for essential_05 or use tokens (cost 30 tokens in client logic)
        $has_essential = $char->hasInInventory('char_essentials', 'essential_05');
        
        if ($has_essential) {
            $char->removeFromInventory('char_essentials', 'essential_05');
        } else {
            $user = $char->user;
            if ($user->tokens < 30) {
                return ["status" => 2, "result" => "Not enough tokens."];
            }
            $user->tokens -= 30;
            $user->save();
        }

        $arena->stamina = 100;
        $arena->save();

        return $this->getData($char_id, $sessionkey);
    }
}
