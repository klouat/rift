<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShopItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['item_id' => 'wpn_01', 'item_type' => 'weapon'],
            ['item_id' => 'wpn_02', 'item_type' => 'weapon'],
            ['item_id' => 'wpn_03', 'item_type' => 'weapon'],
            ['item_id' => 'wpn_04', 'item_type' => 'weapon'],
            ['item_id' => 'wpn_05', 'item_type' => 'weapon'],

            ['item_id' => 'back_01', 'item_type' => 'back'],
            ['item_id' => 'back_02', 'item_type' => 'back'],
            ['item_id' => 'back_03', 'item_type' => 'back'],

            ['item_id' => 'accessory_01', 'item_type' => 'accessory'],
            ['item_id' => 'accessory_02', 'item_type' => 'accessory'],
            ['item_id' => 'accessory_03', 'item_type' => 'accessory'],

            ['item_id' => 'set_01', 'item_type' => 'set'],
            ['item_id' => 'set_02', 'item_type' => 'set'],
            ['item_id' => 'set_03', 'item_type' => 'set'],

            ['item_id' => 'hair_01', 'item_type' => 'hair'],
            ['item_id' => 'hair_02', 'item_type' => 'hair'],
            ['item_id' => 'hair_03', 'item_type' => 'hair'],
        ];

        foreach ($items as $item) {
            \App\Models\ShopItem::updateOrCreate(['item_id' => $item['item_id']], $item);
        }
    }
}
