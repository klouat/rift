<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Pet;
use Illuminate\Support\Facades\Log;

class PetService
{
    private $pet_shop_list = [
        ['swf' => 'pet_helianthus_dragon_III', 'cost' => 'gold_50000', 'name' => 'Helianthus Dragon (III)'],
        ['swf' => 'pet_devil_angel', 'cost' => 'gold_100000', 'name' => 'Devil Angel'],
        ['swf' => 'pet_chocolate_dragon_iv', 'cost' => 'token_100', 'name' => 'Chocolate Dragon (IV)'],
        ['swf' => 'pet_zodiac_leo', 'cost' => 'token_200', 'name' => 'Zodiac: Leo'],
        ['swf' => 'pet_pink_choco_dragon_egg_I', 'cost' => 'token_500', 'name' => 'Pink Choco Dragon Egg I'],
        ['swf' => 'pet_white_choco_dragon_egg_I', 'cost' => 'token_500', 'name' => 'White Choco Dragon Egg I'],
        ['swf' => 'pet_sakura_dragon_girl', 'cost' => 'token_500', 'name' => 'Sakura Dragon Girl'],
        ['swf' => 'pet_heart_monster', 'cost' => 'token_500', 'name' => 'Heart Monster'],
        ['swf' => 'pet_totem_bird', 'cost' => 'token_500', 'name' => 'Totem Bird'],
        ['swf' => 'pet_fall_tree', 'cost' => 'token_500', 'name' => 'Fall Tree'],
        ['swf' => 'pet_ninja_turkey', 'cost' => 'token_500', 'name' => 'Ninja Turkey'],
        ['swf' => 'pet_karasu_tengu', 'cost' => 'token_500', 'name' => 'Karasu Tengu'],
    ];

    private $lighting_pet_shop_list = [
        ['swf' => 'pet_lightning_fox', 'cost' => 'token_500', 'name' => 'Lightning Fox'],
        ['swf' => 'pet_storm_dragon', 'cost' => 'token_800', 'name' => 'Storm Dragon'],
        ['swf' => 'pet_thunder_bird', 'cost' => 'token_1000', 'name' => 'Thunder Bird'],
    ];

    /**
     * PetService uses a generic executeService wrapper.
     */
    public function executeService($method, $params)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$params);
        }

        Log::warning("PetService: Unhandled method {$method}", ['params' => $params]);
        return ['status' => 1]; // Return success stub to prevent client hang
    }

    public function getPetShopData($char_id, $sessionkey)
    {
        $swfs = array_column($this->pet_shop_list, 'swf');
        $costs = array_column($this->pet_shop_list, 'cost');

        return [
            'status' => 1,
            'pets' => $swfs,
            'pets_cost' => $costs
        ];
    }

    public function getLightingPetShopData($char_id, $sessionkey)
    {
        $swfs = array_column($this->lighting_pet_shop_list, 'swf');
        $costs = array_column($this->lighting_pet_shop_list, 'cost');

        return [
            'status' => 1,
            'pets' => $swfs,
            'pets_cost' => $costs
        ];
    }

    public function buyPet($char_id, $sessionkey, $pet_swf)
    {
        $char = Character::find((int) $char_id);
        if (!$char) return ['status' => 0, 'error' => 'Character not found'];

        $shop_item = null;
        $all_shop_pets = array_merge($this->pet_shop_list, $this->lighting_pet_shop_list);
        
        foreach ($all_shop_pets as $item) {
            if ($item['swf'] === $pet_swf) {
                $shop_item = $item;
                break;
            }
        }

        if (!$shop_item) return ['status' => 0, 'error' => 'Pet not in shop'];

        // Check if already owned
        if (Pet::where('char_id', $char->id)->where('pet_swf', $pet_swf)->exists()) {
            return ['status' => 2, 'result' => 'You already own this pet!'];
        }

        $cost = $shop_item['cost'];
        $parts = explode('_', $cost);
        $type = $parts[0];
        $amount = (int) $parts[1];

        if ($type === 'gold') {
            if ($char->gold < $amount) return ['status' => 0, 'error' => 'Not enough gold'];
            $char->gold -= $amount;
        } else {
            $user = $char->user;
            if ($user->tokens < $amount) return ['status' => 0, 'error' => 'Not enough tokens'];
            $user->tokens -= $amount;
            $user->save();
        }

        $char->save();

        $pet = Pet::create([
            'char_id' => $char->id,
            'pet_swf' => $pet_swf,
            'pet_name' => $shop_item['name'],
            'pet_level' => 1,
            'pet_xp' => 0,
            'pet_favorite' => false,
            'pet_mp' => 100,
            'pet_skills' => "1,0,0,0,0,0",
            'pet_weapon' => "",
            'pet_back_item' => "",
            'pet_emblem' => 0
        ]);

        return [
            'status' => 1,
            'result' => 'Pet purchased successfully!'
        ];
    }

    public function getPets($char_id, $sessionkey)
    {
        $char = Character::find((int) $char_id);
        if (!$char) return ['status' => 0, 'result' => 'Character not found'];

        $pets = Pet::where('char_id', $char->id)->get()->map(function($pet) {
            return [
                'pet_id' => $pet->id,
                'pet_swf' => $pet->pet_swf,
                'pet_name' => $pet->pet_name,
                'pet_level' => (string) $pet->pet_level,
                'pet_xp' => (string) $pet->pet_xp,
                'pet_favorite' => (bool) $pet->pet_favorite,
                'pet_mp' => (string) $pet->pet_mp,
                'pet_skills' => (string) $pet->pet_skills,
                'arena_skills_learnt' => array_map('intval', explode(',', $pet->pet_skills ?? "1,0,0,0,0,0")),
                'pet_weapon' => (string) $pet->pet_weapon,
                'pet_back_item' => (string) $pet->pet_back_item,
                'pet_emblem' => (int) $pet->pet_emblem
            ];
        })->toArray();

        return [
            'status' => 1,
            'pets'   => $pets,
            'pet_id' => (int) $char->pet_id
        ];
    }

    public function getFeedablePets($char_id, $sessionkey)
    {
        // For now, return all pets as feedable
        $char = Character::find((int) $char_id);
        if (!$char) return [];
        return Pet::where('char_id', $char_id)->pluck('pet_swf')->toArray();
    }

    public function getArenaPets($char_id, $sessionkey)
    {
        // For now, allow all pets in arena
        $char = Character::find((int) $char_id);
        if (!$char) return [];
        return Pet::where('char_id', $char_id)->pluck('pet_swf')->toArray();
    }

    public function toggleFavorite($char_id, $sessionkey, $pet_id)
    {
        $pet = Pet::where('char_id', (int)$char_id)->where('id', (int)$pet_id)->first();
        if ($pet) {
            $pet->pet_favorite = !$pet->pet_favorite;
            $pet->save();
        }
        return $this->getPets($char_id, $sessionkey);
    }

    public function changePetGear($char_id, $sessionkey, $type, $gear_id, $pet_id)
    {
        $pet = Pet::where('char_id', (int)$char_id)->where('id', (int)$pet_id)->first();
        if ($pet) {
            if ($type == 'weapon') {
                $pet->pet_weapon = $gear_id;
            } else {
                $pet->pet_back_item = $gear_id;
            }
            $pet->save();
        }

        return [
            'status' => 1,
            'data'   => [
                'pet_weapon'    => $pet ? (string)$pet->pet_weapon : '',
                'pet_back_item' => $pet ? (string)$pet->pet_back_item : ''
            ]
        ];
    }

    public function equipPet($char_id, $sessionkey, $pet_id)
    {
        $char = Character::find((int) $char_id);
        if (!$char) return ['status' => 0, 'result' => 'Character not found'];

        $pet = Pet::where('char_id', $char->id)->where('id', (int)$pet_id)->first();
        if (!$pet) return ['status' => 0, 'result' => 'Pet not found'];

        $char->pet_id = (int)$pet_id;
        $char->save();

        return [
            'status' => 1,
            'pet_id' => $pet->id,
            'pet_swf' => $pet->pet_swf
        ];
    }

    public function unequipPet($char_id, $sessionkey)
    {
        $char = Character::find((int) $char_id);
        if (!$char) return ['status' => 0, 'result' => 'Character not found'];

        $char->pet_id = 0;
        $char->save();

        return ['status' => 1];
    }

    public function feedPet($char_id, $sessionkey, $pet_id, $item_id)
    {
        $char = Character::find((int) $char_id);
        if (!$char) return ['status' => 0, 'result' => 'Character not found'];

        $pet = Pet::where('char_id', (int)$char_id)->where('id', (int)$pet_id)->first();
        if (!$pet) return ['status' => 0, 'result' => 'Pet not found'];

        if (!$char->removeFromInventory('char_essentials', $item_id, 1)) {
            return ['status' => 0, 'result' => 'Not enough food'];
        }

        // Dummy logic for feed amount
        $feed_amt = 10;
        if (str_contains($item_id, 'essential_39')) $feed_amt = 30;
        elseif (str_contains($item_id, 'essential_38')) $feed_amt = 20;

        $pet->pet_mp += $feed_amt;
        if ($pet->pet_mp > 100) $pet->pet_mp = 100;
        $pet->save();

        return [
            'status' => 1,
            'result' => 'Pet fed successfully!',
            'item_id' => $item_id,
            'feed_amt' => $feed_amt
        ];
    }

    public function learnSkill($char_id, $sessionkey, $pet_id, $skill_number, $mode)
    {
        $char = Character::find((int) $char_id);
        if (!$char) return ['status' => 0, 'result' => 'Character not found'];

        $pet = Pet::where('char_id', (int)$char_id)->where('id', (int)$pet_id)->first();
        if (!$pet) return ['status' => 0, 'result' => 'Pet not found'];

        // Mode mc1 = Gold/Materials, mc2 = Tokens
        // For now, let's just deduct a flat fee
        if ($mode === 'mc1') {
            if ($char->gold < 50000) return ['status' => 0, 'result' => 'Not enough gold'];
            $char->gold -= 50000;
        } else {
            $user = $char->user;
            if ($user->tokens < 50) return ['status' => 0, 'result' => 'Not enough tokens'];
            $user->tokens -= 50;
            $user->save();
        }

        $char->save();

        // Add skill to pet_skills string
        $skills = $pet->pet_skills ? explode(',', $pet->pet_skills) : [];
        $new_skill = "skill_" . ($skill_number + 1);
        if (!in_array($new_skill, $skills)) {
            $skills[] = $new_skill;
            $pet->pet_skills = implode(',', $skills);
            $pet->save();
        }

        return ['status' => 1, 'result' => 'Skill learned!'];
    }

    public function getCombinablePets($char_id, $sessionkey)
    {
        $char = Character::find((int) $char_id);
        if (!$char) return ['status' => 0, 'error' => 'Character not found'];

        // Combinable pets usually need level 20 and 100 MP
        $pets = Pet::where('char_id', $char->id)
            ->where('pet_level', '>=', 20)
            ->where('pet_mp', '>=', 100)
            ->get()->map(function($pet) {
                return [
                    'pet_id' => $pet->id,
                    'pet_swf' => $pet->pet_swf,
                    'pet_name' => $pet->pet_name,
                    'pet_level' => (string)$pet->pet_level,
                    'pet_mp' => (string)$pet->pet_mp
                ];
            });

        return [
            'status' => 1,
            'pets'   => $pets
        ];
    }

    public function combinePets($char_id, $sessionkey, $pet_ids, $method)
    {
        $char = Character::find((int) $char_id);
        if (!$char) return ['status' => 0, 'result' => 'Character not found'];

        $pet1 = Pet::where('char_id', $char->id)->where('id', $pet_ids[0])->first();
        $pet2 = Pet::where('char_id', $char->id)->where('id', $pet_ids[1])->first();

        if (!$pet1 || !$pet2) return ['status' => 0, 'result' => 'One or more pets not found'];

        // Deduct cost
        if ($method == 1) { // Boost (Tokens)
            $user = $char->user;
            if ($user->tokens < 300) return ['status' => 0, 'result' => 'Not enough tokens'];
            $user->tokens -= 300;
            $user->save();
        } else {
            if ($char->gold < 1000000) return ['status' => 0, 'result' => 'Not enough gold'];
            $char->gold -= 1000000;
        }

        $char->save();

        // Success rate: 100% for boost, 50% for normal
        $success = ($method == 1) ? true : (rand(1, 100) <= 50);

        if ($success) {
            // Logic for new pet: take swf of one or a better tier
            // For now, let's just give a random "tier 2" pet swf or something
            $new_swf = $pet1->pet_swf . "_ii"; // Dummy evolution string
            
            // Remove old pets
            $pet1->delete();
            $pet2->delete();

            // Create new pet
            Pet::create([
                'char_id' => $char->id,
                'pet_swf' => $new_swf,
                'pet_name' => 'Evolved Pet',
                'pet_level' => 1
            ]);

            return [
                'status' => 1,
                'result_combine' => true,
                'new_pet_swf' => $new_swf
            ];
        } else {
            return [
                'status' => 2, // Fail but still status 1/2 handled by client
                'result_combine' => false,
                'result' => 'Combination failed. Your pets were lost in the process.'
            ];
        }
    }
}
