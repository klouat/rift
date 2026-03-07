<?php

namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Facades\Log;

class ScratchCard
{
    private $rewards = [
        'tokens_10', 'tokens_20', 'tokens_50', 
        'gold_1000', 'gold_5000', 'gold_10000',
        'material_01', 'material_02', 'material_03',
        'essential_125'
    ];

    public function executeService($method, $params)
    {
        switch ($method) {
            case 'getData':
                return $this->getData($params[0], $params[1]);
            case 'scratch':
                return $this->scratch($params[0], $params[1], $params[2], $params[3]);
            default:
                return ['status' => 0, 'result' => 'Unknown method in executeService'];
        }
    }

    private function getData($char_id, $sessionkey)
    {
        return [
            'status' => 1,
            'data'   => (object)[],
            'reward' => '',
            'selectedScratch' => ''
        ];
    }

    private function scratch($char_id, $sessionkey, $scratch_name, $mode)
    {
        $char = Character::find((int) $char_id);
        if (!$char) {
            return ['status' => 0, 'result' => 'Character not found.'];
        }

        $user = $char->user;
        if (!$user) {
            return ['status' => 0, 'result' => 'User not found.'];
        }

        if ($mode === 'tokens') {
            if ($user->tokens < 10) {
                return ['status' => 0, 'selectedScratch' => '', 'data' => ['result' => 'Not enough Tokens.']];
            }
            $user->tokens -= 10;
            $user->save();
        }

        $reward = $this->rewards[array_rand($this->rewards)];

        // Handle giving reward based on type
        if (strpos($reward, 'tokens_') === 0) {
            $amt = (int) str_replace('tokens_', '', $reward);
            $user->tokens += $amt;
            $user->save();
        } elseif (strpos($reward, 'gold_') === 0) {
            $amt = (int) str_replace('gold_', '', $reward);
            $char->gold += $amt;
            $char->save();
        } else {
            // Assume materials/essentials inventory
            if (strpos($reward, 'material_') === 0) {
                $char->addToInventory('char_materials', $reward, 1);
            } else {
                $char->addToInventory('char_essentials', $reward, 1);
            }
            $char->save();
        }

        return [
            'status'          => 1,
            'data'            => (object)[],
            'reward'          => $reward,
            'selectedScratch' => $scratch_name,
            'is_levelup'      => false
        ];
    }
}
