<?php

namespace App\Traits;

use App\Models\Character;
use App\Models\Pet;
use Illuminate\Support\Facades\Log;

trait RewardHandler
{
    use LevelManager, PetLevelManager;

    /**
     * awardReward — Centralized reward distribution.
     * $rewardConfig = [
     *   'xp'    => 1000,
     *   'gold'  => 5000,
     *   'items' => ['wpn_101', 'material_01'], // Fixed items
     *   'rolls' => 3,                          // Number of random rolls
     *   'pool'  => ['mat_01', 'wpn_201'],      // Random pool
     *   'pets'  => ['pet_01']                 // Direct pets
     * ]
     */
    public function awardReward(Character $char, array $rewardConfig): array
    {
        $xp_gain = (int)($rewardConfig['xp'] ?? 0);
        $gold_gain = (int)($rewardConfig['gold'] ?? 0);

        // 1. Award XP and handle level up
        $awards = $this->awardXp($char, $xp_gain);
        $char->xp = $awards['xp'];
        $char->level = $awards['level'];

        // 1.5. Award XP to equipped pet (if any)
        if ($char->pet_id > 0) {
            $equipped_pet = Pet::find($char->pet_id);
            if ($equipped_pet) {
                // Pet gets 20% of chars XP, capped by char level
                $this->awardPetXp($equipped_pet, $xp_gain, $char->level);
            }
        }

        // 2. Award Gold
        $char->gold = (int)$char->gold + $gold_gain;

        $granted_items = [];

        // 3. Process static items
        if (isset($rewardConfig['items'])) {
            foreach ($rewardConfig['items'] as $item_id) {
                $this->processSingleReward($char, $item_id, $granted_items);
            }
        }

        // 4. Process random rolls from pool
        if (isset($rewardConfig['pool']) && !empty($rewardConfig['pool'])) {
            $rolls = $rewardConfig['rolls'] ?? 1;
            $pool = $rewardConfig['pool'];
            for ($i = 0; $i < $rolls; $i++) {
                $item_id = $pool[array_rand($pool)];
                $this->processSingleReward($char, $item_id, $granted_items);
            }
        }

        // 5. Process direct pets
        if (isset($rewardConfig['pets'])) {
            foreach ($rewardConfig['pets'] as $pet_swf) {
                $this->processSingleReward($char, $pet_swf, $granted_items);
            }
        }

        $char->save();

        return [
            'status'   => 1,
            'level'    => $char->level,
            'xp'       => $char->xp,
            'level_up' => $awards['level_up'],
            'result'   => [
                (string) $awards['xp_gain'],
                (string) $gold_gain,
                $granted_items,
                $awards['level_up'],
            ],
        ];
    }

    /**
     * processSingleReward — Detects item type and adds to character.
     */
    public function processSingleReward(Character $char, string $item_id, array &$granted_items): void
    {
        // Skip null/empty/invalid items
        if (empty($item_id) || $item_id === 'mat_01' || $item_id === 'null' || str_ends_with($item_id, '_')) {
            return;
        }

        try {
            if (str_starts_with($item_id, 'pet_')) {
                // Check if already owns this pet SWF
                if (!Pet::where('char_id', $char->id)->where('pet_swf', $item_id)->exists()) {
                    Pet::create([
                        'char_id' => $char->id,
                        'pet_swf' => $item_id,
                        'pet_name' => ucfirst(str_replace(['pet_', '_'], ['', ' '], $item_id)),
                        'pet_level' => 1,
                        'pet_skills' => '1,0,0,0,0,0'
                    ]);
                }
                $granted_items[] = $item_id;
            } elseif (str_starts_with($item_id, 'tokens_')) {
                $p = explode('_', $item_id);
                $amt = (int)$p[1];
                $user = $char->user;
                if ($user) {
                    $user->tokens += $amt;
                    $user->save();
                }
                $granted_items[] = $item_id;
            } elseif (str_starts_with($item_id, 'gold_')) {
                $p = explode('_', $item_id);
                $amt = (int)$p[1];
                $char->gold += $amt;
                $granted_items[] = $item_id;
            } else {
                $category = $this->getCategoryByItemId($item_id);
                // Handle cases like essential_141_3 (quantity packed in name)
                $qty = 1;
                $final_id = $item_id;
                $parts = explode('_', $item_id);
                if (count($parts) >= 3 && is_numeric(end($parts))) {
                    $qty = (int)array_pop($parts);
                    $final_id = implode('_', $parts);
                }

                $char->addToInventory($category, $final_id, $qty);
                $granted_items[] = $item_id; // Return original ID for client UI
            }
        } catch (\Exception $e) {
            Log::error("Failed to award single reward", [
                'char_id' => $char->id,
                'item_id' => $item_id,
                'error'   => $e->getMessage()
            ]);
        }
    }

    private function getCategoryByItemId(string $item_id): string
    {
        if (str_starts_with($item_id, 'wpn_')) return 'char_weapons';
        if (str_starts_with($item_id, 'back_')) return 'char_back_items';
        if (str_starts_with($item_id, 'acc_')) return 'char_accessories';
        if (str_starts_with($item_id, 'set_')) return 'char_sets';
        if (str_starts_with($item_id, 'essential_')) return 'char_essentials';
        if (str_starts_with($item_id, 'material_') || str_starts_with($item_id, 'mat_')) return 'char_materials';
        if (str_starts_with($item_id, 'skill_')) return 'char_skills';
        if (str_starts_with($item_id, 'hair_')) return 'char_hairs';
        return 'char_items';
    }
}
