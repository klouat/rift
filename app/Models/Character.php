<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    protected $fillable = [
        'account_id', 'name', 'level', 'gold', 'profile_pic',
        'gender', 'element', 'element_2', 'element_3', 'element_4', 'element_5', 'hair_style_color', 'hair_num', 'skin_color',
        // Stats
        'xp', 'rank', 'hp', 'cp', 'tp', 'agility', 'dodge', 'critical', 'purify',
        // Equipped Slots
        'equipped_weapon', 'equipped_back_item', 'equipped_accessory', 
        'equipped_clothing', 'equipped_hairstyle', 'equipped_skills',
        // Inventories
        'char_weapons', 'char_pet_weapons', 'char_back_items', 'char_pet_back_items',
        'char_accessories', 'char_sets', 'char_hairs', 'char_skills', 'char_materials', 
        'char_essentials', 'char_talent_skills', 'char_senjutsu_skills',
        'char_talent_1', 'char_talent_2', 'char_talent_3',
        // Extras
        'character_class', 'village_id', 'level_up_packages',
        // Attributes
        'atrrib_wind', 'atrrib_fire', 'atrrib_lightning', 'atrrib_water', 'atrrib_earth', 'atrrib_free'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'account_id');
    }

    public function valentineEvent()
    {
        return $this->hasOne(ValentineEvent2026::class, 'char_id');
    }

    public function hanamiEvent()
    {
        return $this->hasOne(HanamiEvent2026::class, 'char_id');
    }



    /**
     * Parses a colon-separated inventory string into an associative array [id => qty].
     */
    public function getInventoryArray(string $column): array
    {
        $raw = $this->getAttribute($column);
        if (!$raw) return [];
        
        $items = [];
        $parts = explode(',', $raw);
        foreach ($parts as $part) {
            if (strpos($part, ':') !== false) {
                list($id, $qty) = explode(':', $part);
                $items[$id] = (int)$qty;
            } else {
                $items[$part] = 1;
            }
        }
        return $items;
    }

    /**
     * Serializes an associative array [id => qty] back to the inventory string.
     */
    public function setInventoryArray(string $column, array $items): void
    {
        $parts = [];
        foreach ($items as $id => $qty) {
            if ($qty > 1 || in_array($column, ['char_weapons', 'char_back_items', 'char_accessories', 'char_sets', 'char_essentials', 'char_materials'])) {
                $parts[] = "{$id}:{$qty}";
            } else {
                $parts[] = $id;
            }
        }
        $this->setAttribute($column, implode(',', $parts));
    }

    public function addToInventory(string $column, string $id, int $qty = 1): void
    {
        $items = $this->getInventoryArray($column);
        $items[$id] = ($items[$id] ?? 0) + $qty;
        $this->setInventoryArray($column, $items);
    }

    public function removeFromInventory(string $column, string $id, int $qty = 1): bool
    {
        $items = $this->getInventoryArray($column);
        if (!isset($items[$id]) || $items[$id] < $qty) {
            return false;
        }
        
        $items[$id] -= $qty;
        if ($items[$id] <= 0) {
            unset($items[$id]);
        }
        
        $this->setInventoryArray($column, $items);
        return true;
    }
}
