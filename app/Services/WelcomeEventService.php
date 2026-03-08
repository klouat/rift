<?php

namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Facades\Log;

class WelcomeEventService
{
    use \App\Traits\SessionValidator;

    /**
     * claimFreePackage - Claims the welcome package rewards.
     *
     * ActionScript (Package_Welcome.as:29):
     * this.main.amf_manager.service("Event_Welcome.claimFreePackage",[Character.sessionkey,Character.char_id],this.claimResponse);
     *
     * Result Statuses:
     * 1 - Success
     * 2 - Already claimed
     */
    public function claimFreePackage($sessionkey, $char_id): array
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof Character)) {
            return $char;
        }

        if ($char->welcome_claimed) {
            return ['status' => 2];
        }

        try {
            // Rewards from Package_Welcome.as:38-42
            // wpn_303, back_43, set_103_GENDER, hair_33_GENDER, 100 tokens
            
            $char->addToInventory('char_weapons', 'wpn_303', 1);
            $char->addToInventory('char_back_items', 'back_43', 1);
            $char->addToInventory('char_sets', 'set_103', 1);
            $char->addToInventory('char_hairs', 'hair_33', 1);

            $user = $char->user;
            $user->tokens += 100;
            $user->save();

            $char->welcome_claimed = true;
            $char->save();

            return ['status' => 1];

        } catch (\Exception $e) {
            Log::error("Failed to claim welcome package", [
                'char_id' => $char_id,
                'error' => $e->getMessage()
            ]);
            return [
                'status' => 0,
                'error' => "An error occurred while claiming rewards."
            ];
        }
    }
}
