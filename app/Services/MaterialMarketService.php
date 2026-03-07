<?php

namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Facades\Log;

class MaterialMarketService
{
    public function getForgeList($char_id, $sessionkey, $gender)
    {
        // Sample forge data for Hanami/Valentine materials
        // Structure: ItemID => [ [Materials], [Quantities] ]
        
        $forgeDataWeapons = [
            'wpn_722' => [
                ['material_66', 'material_346'],
                [10, 5]
            ]
        ];

        $forgeDataPets = [];
        $forgeDataBackItems = [];
        $forgeDataAccessories = [];
        $forgeDataSkills = [];
        $forgeDataHairs = [];
        $forgeDataSets = [];

        return [
            'weaponList' => array_keys($forgeDataWeapons),
            'petList'    => array_keys($forgeDataPets),
            'backList'   => array_keys($forgeDataBackItems),
            'accList'    => array_keys($forgeDataAccessories),
            'skillList'  => array_keys($forgeDataSkills),
            'hairList'   => array_keys($forgeDataHairs),
            'setList'    => array_keys($forgeDataSets),
            
            'forgeDataWeapons'      => $forgeDataWeapons,
            'forgeDataPets'         => $forgeDataPets,
            'forgeDataBackItems'    => $forgeDataBackItems,
            'forgeDataAccessories'  => $forgeDataAccessories,
            'forgeDataSkills'       => $forgeDataSkills,
            'forgeDataHairs'        => $forgeDataHairs,
            'forgeDataSets'         => $forgeDataSets,
        ];
    }

    public function forgeItem($sessionkey, $char_id, $item_id)
    {
        $char = Character::find((int)$char_id);
        if (!$char) {
            return ['status' => 0, 'error' => 'Character not found'];
        }

        // Logic for forging would go here (checking materials, reducing them, adding item)
        // For now, let's just return a generic success for testing
        
        return [
            'status' => 1,
            'item' => $item_id,
            'requirements' => [ [], [], 0 ] // [materials], [quantities], tokens
        ];
    }
}
