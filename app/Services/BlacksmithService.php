<?php

namespace App\Services;

class BlacksmithService
{
    use \App\Traits\SessionValidator;

    // Define the recipes for forging, easily expandable.
    // Format: 'item_id' => [['material_1', 'material_2'], [qty_1, qty_2], token_price]
    private $forgeData = [
        'wpn_01' => [['material_01', 'material_02'], [5, 2], 100],
        'back_01' => [['material_01', 'material_03'], [10, 5], 150],
        'accessory_01' => [['material_02', 'material_03'], [3, 3], 80],
    ];

    public function getForgeList($char_id, $sessionkey): array
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof \App\Models\Character)) return $char;

        return [
            'status'     => 1,
            'weaponList' => ['wpn_01'],
            'backList'   => ['back_01'],
            'accList'    => ['accessory_01'],
            'setList'    => [],
            'hairList'   => [],
            'forgeData'  => $this->forgeData
        ];
    }

    public function forgeItem($sessionkey, $char_id, $item_id): array
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof \App\Models\Character)) return $char;

        if (!isset($this->forgeData[$item_id])) {
            return ['status' => 0, 'error' => 'Invalid item.'];
        }

        // Implementation for material checking and deduction goes here
        $recipe = $this->forgeData[$item_id];
        
        return [
            'status' => 1,
            'item' => $item_id,
            'requirements' => $recipe
        ];
    }

    public function forgeItemWithTokens($sessionkey, $char_id, $item_id): array
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof \App\Models\Character)) return $char;

        if (!isset($this->forgeData[$item_id])) {
            return ['status' => 0, 'error' => 'Invalid item.'];
        }

        $recipe = $this->forgeData[$item_id];
        $token_price = $recipe[2];

        // Token deduction logic
        
        return [
            'status' => 1,
            'item' => $item_id,
            'requirements' => $recipe
        ];
    }
}
