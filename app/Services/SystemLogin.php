<?php

namespace App\Services;

use App\Models\User;
use App\Models\Character;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SystemLogin {

    public function checkVersion($build_num) {
        return ["status" => 1];
    }

    public function registerUser($username, $email, $password, $verification = null, $referral = null) {
        if (User::where('username', $username)->exists()) {
            return ["status" => 2]; // username taken
        }

        if (User::where('email', $email)->exists()) {
            return ["status" => 3]; // email taken
        }

        User::create([
            'username' => $username,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        return ["status" => 1]; // success
    }

    public function loginUser($username, $password, $deviceId = null) {
        $user = User::where('username', $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return ["status" => 2]; // wrong credentials
        }

        $sessionkey = md5(time() . Str::random(10) . $username);

        return [
            "status"               => 1,
            "uid"                  => $user->id,
            "sessionkey"           => $sessionkey,
            "verified"             => 1,
            "news"                 => "",
            "showReturningVeteran" => 0,
            "veteranRewards"       => [],
            "pvpPort"              => 0,
            "pvePort"              => 0,
            "pve_season"           => 0,
            "clan_season"          => 0,
            "crew_season"          => 0,
        ];
    }

    public function getAllCharacters($account_id, $sessionkey) {
        $user = User::with('characters')->find($account_id);
        
        if (!$user) {
            return ["status" => 2, "error" => "User not found."]; // 2 usually triggers character creation
        }

        $characters = $user->characters;

        if ($characters->isEmpty()) {
            return [
                "status" => 2,
                "total_characters" => 0,
                "account_type" => $user->account_type,
                "tokens" => $user->tokens
            ];
        }

        $account_data = [];
        foreach ($characters as $char) {
            $account_data[] = [
                'char_id' => $char->id,
                'character_name' => $char->name,
                'character_level' => $char->level,
                'character_xp' => $char->xp,
                'character_gender' => $char->gender,
                'character_rank' => $char->rank,
                'character_element_1' => (int) $char->element,
                'character_element_2' => (int) ($char->element_2 ?? 0),
                'character_element_3' => (int) ($char->element_3 ?? 0),
                'character_element_4' => (int) ($char->element_4 ?? 0),
                'character_element_5' => (int) ($char->element_5 ?? 0),
                'character_talent_1' => $char->char_talent_1 ?? "",
                'character_talent_2' => $char->char_talent_2 ?? "",
                'character_talent_3' => $char->char_talent_3 ?? "",
                'character_gold' => $char->gold,
                'character_tp' => $char->tp,
                'character_class' => $char->character_class,
                'character_village_id' => $char->village_id,
                'character_sets' => $char->char_sets // Will be JSON Decoded natively by DB
            ];
        }

        return [
            "status" => 1,
            "total_characters" => $characters->count(),
            "account_type" => $user->account_type,
            "tokens" => $user->tokens,
            "account_data" => $account_data
        ];
    }

    public function getCharacterData($char_id, $sessionkey_or_type, $type="self") {
        $char = Character::find($char_id);
        
        if ($char) {
            // Build the massive object that Character.as expects
            return [
                "status" => 1,
                "error"  => 0,
                "teammate_controllable" => 0,
                "is_day" => true,
                "new_mails" => false,
                "daily_task_completed" => false,
                "events" => [
                    "welcome_bonus" => 0,
                    "social1" => 0,
                    "social2" => 0,
                    "social3" => 0,
                    "social4" => 0,
                ],
                "character_data" => [
                    "character_name"       => $char->name,
                    "character_level"      => (int) $char->level,
                    "character_xp"         => (int) $char->xp,
                    "character_gender"     => (int) $char->gender,
                    "character_rank"       => (int) $char->rank,
                    "character_element_1"  => (int) $char->element,
                    "character_element_2"  => (int) ($char->element_2 ?? 0),
                    "character_element_3"  => (int) ($char->element_3 ?? 0),
                    "character_element_4"  => (int) ($char->element_4 ?? 0),
                    "character_element_5"  => (int) ($char->element_5 ?? 0),
                    "character_talent_1"   => $char->char_talent_1 ?: null,
                    "character_talent_2"   => $char->char_talent_2 ?: null,
                    "character_talent_3"   => $char->char_talent_3 ?: null,
                    "character_gold"       => (string) $char->gold,
                    "character_tp"         => (int) ($char->tp ?? 0),
                    "character_class"      => (string) ($char->character_class ?? ""),
                    "character_village_id" => (int) ($char->village_id ?? 0),
                    "unlocked_villages"    => [],
                ],
                "character_points" => [
                    "atrrib_wind"      => (int) $char->atrrib_wind,
                    "atrrib_fire"      => (int) $char->atrrib_fire,
                    "atrrib_lightning" => (int) $char->atrrib_lightning,
                    "atrrib_water"     => (int) $char->atrrib_water,
                    "atrrib_earth"     => (int) $char->atrrib_earth,
                    "atrrib_free"      => (int) $char->atrrib_free
                ],
                "character_slots" => [
                    "weapons"     => 100,
                    "back_items"  => 100,
                    "accessories" => 100,
                    "hairstyles"  => 100,
                    "clothing"    => 100
                ],
                "character_sets" => [
                    "weapon"      => $char->equipped_weapon,
                    "back_item"   => $char->equipped_back_item,
                    "accessory"   => $char->equipped_accessory,
                    "clothing"    => $char->equipped_clothing,
                    "hairstyle"   => $char->equipped_hairstyle,
                    "skills"      => $char->equipped_skills,
                    "hair_color"  => $char->hair_style_color,
                    "skin_color"  => $char->skin_color,
                    "face"        => 'face_01_' . $char->gender,
                    "pet"         => null,
                    "skill_preset"=> null,
                    "preset_name" => "Preset 1"
                ],
                "character_inventory" => [
                    "char_weapons"         => $char->char_weapons ?? "wpn_01:1",
                    "char_pet_weapons"     => $char->char_pet_weapons ?? "",
                    "char_pet_back_items"  => $char->char_pet_back_items ?? "",
                    "char_back_items"      => $char->char_back_items ?? "back_01:1",
                    "char_accessories"     => $char->char_accessories ?? "accessory_01:1",
                    "char_sets"            => $char->char_sets ?? "set_01_0:1",
                    "char_hairs"           => $char->char_hairs ?? "hair_01_0",
                    "char_skills"          => $char->char_skills ?? "skill_13",
                    "char_talent_skills"   => $char->char_talent_skills ?? "",
                    "char_senjutsu_skills" => $char->char_senjutsu_skills ?? "",
                    "char_materials"       => $char->char_materials ?? "",
                    "char_essentials"      => $char->char_essentials ?? ""
                ],
                "recruiters" => [],
                "recruit_data" => [
                    ["id" => 999873, "char_id" => (int)$char->id, "recruiter_id" => "npc_3", "amount" => 5],
                    ["id" => 999874, "char_id" => (int)$char->id, "recruiter_id" => "npc_4", "amount" => 5],
                    ["id" => 999875, "char_id" => (int)$char->id, "recruiter_id" => "npc_5", "amount" => 5],
                    ["id" => 999876, "char_id" => (int)$char->id, "recruiter_id" => "npc_6", "amount" => 5],
                ],
                "pet_data"   => [],
                "arena_data" => [
                    "char_id"         => (int) $char->id,
                    "stamina"         => 100,
                    "max_stamina"     => 100,
                    "village_id"      => (int) ($char->village_id ?? 4),
                    "trophies"        => 0,
                    "enemy_id"        => -1,
                    "first_open"      => 1,
                    "village_changed" => 0,
                    "arena_hash"      => "f35ce247e15061487a28c8836822852ebe13874a032c3800e65e7d9acbb0802a"
                ]
            ];
        } else {
            return ["status" => 0, "error" => "Character not found."];
        }
    }
}
