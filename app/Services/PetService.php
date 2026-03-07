<?php

namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Facades\Log;

class PetService
{
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

    public function getPets($char_id, $sessionkey)
    {
        $char = Character::find((int) $char_id);
        if (!$char) {
            return ['status' => 0, 'result' => 'Character not found'];
        }

        return [
            'status' => 1,
            'pets'   => [] 
        ];
    }

    public function getFeedablePets($char_id, $sessionkey)
    {
        return [];
    }

    public function getArenaPets($char_id, $sessionkey)
    {
        return [];
    }

    public function toggleFavorite($char_id, $sessionkey, $pet_id)
    {
        // For now just return the current (empty) pets list
        return $this->getPets($char_id, $sessionkey);
    }

    public function changePetGear($char_id, $sessionkey, $type, $gear_id, $pet_id)
    {
        return [
            'status' => 1,
            'data'   => [
                'pet_weapon'    => '',
                'pet_back_item' => ''
            ]
        ];
    }
}
