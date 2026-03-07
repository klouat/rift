<?php

namespace App\Services;

use App\Models\Character;
use App\Models\MerchantPackage;
use App\Models\CharacterBlackMerchant;
use Illuminate\Support\Facades\Log;

class BlackMerchant
{
    use \App\Traits\SessionValidator;

    public function executeService($method, $params)
    {
        switch ($method) {
            case 'getData':
                return $this->getData($params[0], $params[1]);
            case 'buySkill':
                return $this->buySkill($params[0], $params[1]);
            case 'resetSkill':
                return $this->resetSkill($params[0], $params[1]);
            default:
                return ['status' => 0, 'result' => 'Unknown method in BlackMerchant'];
        }
    }

    private function getData($char_id, $sessionkey)
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        // 1. Get or create character shop state
        $shopState = CharacterBlackMerchant::firstOrCreate(
            ['char_id' => $char->id],
            [
                'package_id' => 'package_10', // Default starting package
                'refreshed_at' => now()->subDays(1) // Simulate it happened yesterday
            ]
        );

        // 2. Fetch ALL packages for the library view
        // The client needs the full details to populate the "Collection" panel.
        $allPackages = MerchantPackage::all();
        $packageDatas = [];
        $packageIds = [];

        foreach ($allPackages as $pkg) {
            $packageIds[] = $pkg->package_id;
            $packageDatas[$pkg->package_id] = [
                'skills' => $pkg->skills,
                'prices' => $pkg->prices,
            ];
            if ($pkg->advanced_skills) {
                $packageDatas[$pkg->package_id]['advanced_skills'] = $pkg->advanced_skills;
            }
        }

        // 3. Construct response
        // cooldown timestamp: released_timestamp + 604800 (1 week) - current_timestamp
        $refreshedTimestamp = $shopState->refreshed_at->timestamp;
        $currentTime = time();

        return [
            'status' => 1,
            'error'  => 0,
            'mt'     => false,
            'data'   => [
                'id' => $shopState->id,
                'char_id' => (int)$char->id,
                'package_id' => $shopState->package_id,
                'refreshed_timestamp' => $refreshedTimestamp,
                'timestamp' => $currentTime,
                'packages' => [
                    'packages' => $packageIds,
                ],
                'package_datas' => (object)$packageDatas
            ],
            'packages' => [
                'packages' => $packageIds,
                'package_datas' => (object)$packageDatas
            ],
            'extra_data' => []
        ];
    }

    private function buySkill($char_id, $sessionkey)
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        $shopState = CharacterBlackMerchant::where('char_id', $char->id)->first();
        if (!$shopState) return ['status' => 0, 'result' => 'Shop state not found.'];

        $pkg = MerchantPackage::where('package_id', $shopState->package_id)->first();
        if (!$pkg) return ['status' => 0, 'result' => 'Package not found.'];
        
        $skills = $pkg->skills;
        $prices = $pkg->prices;
        
        $target_skill = null;
        $target_price = 0;
        $is_upgrade = 0;
        $remove_skill_id = '';

        // Find the first unowned skill in the sequence
        foreach ($skills as $idx => $sid) {
            if (!$this->isOwned($char, $sid)) {
                $target_skill = $sid;
                // Price indexing might be tricky if prices count != skills count, adding safety
                $target_price = $prices[$idx] ?? end($prices);
                break;
            }
        }

        if (!$target_skill) {
            return ['status' => 2, 'result' => 'You already own all skills in this package.'];
        }

        // Emblem discount (50%)
        if ($char->user->account_type > 0) {
            $target_price = (int)(($target_price + 1) / 2 - 1);
        }

        if ($char->user->tokens < $target_price) {
            return ['status' => 0, 'result' => 'Not enough tokens.'];
        }

        $char->user->tokens -= $target_price;
        $char->user->save();

        $char->addToInventory('char_skills', $target_skill);
        $char->save();

        return [
            'status' => 1,
            'skill_id' => $target_skill,
            'price' => $target_price,
            'is_upgrade' => $is_upgrade,
            'remove_skill_id' => $remove_skill_id,
            'skills' => $char->char_skills
        ];
    }

    private function resetSkill($char_id, $sessionkey)
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) return $char;

        // Costs 50 tokens
        if ($char->user->tokens < 50) {
            return ['status' => 0, 'result' => 'Not enough tokens.'];
        }

        $char->user->tokens -= 50;
        $char->user->save();

        // Pick a random package different from the current one
        $shopState = CharacterBlackMerchant::where('char_id', $char->id)->first();
        $newPkg = MerchantPackage::where('package_id', '!=', $shopState->package_id)
            ->inRandomOrder()
            ->first();

        if ($newPkg) {
            $shopState->package_id = $newPkg->package_id;
            $shopState->save();
        }

        $data = $this->getData($char_id, $sessionkey);
        $data['extra_data'] = [
            'message' => 'Reset skills successful!',
            'reduce_tokens' => 50,
            'reward' => [],
            'remove_skill' => '',
            'data_skill' => 'false'
        ];
        return $data;
    }

    private function isOwned($char, $skill_id)
    {
        $owned = explode(',', $char->char_skills);
        return in_array($skill_id, $owned);
    }
}
