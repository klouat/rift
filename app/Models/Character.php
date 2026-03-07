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
        'char_talent_1', 'char_talent_2', 'char_talent_3',
        // Extras
        'character_class', 'village_id', 'level_up_packages',
        // Attributes
        'atrrib_wind', 'atrrib_fire', 'atrrib_lightning', 'atrrib_water', 'atrrib_earth', 'atrrib_free',
        'ss_points', 'wt_spins', 'wt_total_spins', 'wt_today_spins', 'wt_last_spin', 'dr_day', 'dr_last_spin',
        'ed_tokens_last_claim', 'ed_xp_last_claim', 'ed_skills_last_claim', 'ed_double_xp'
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

    public function items()
    {
        return $this->hasMany(CharacterItem::class, 'character_id');
    }

    public function skills()
    {
        return $this->hasMany(CharacterSkill::class, 'character_id');
    }

    public function talentSkills()
    {
        return $this->hasMany(CharacterTalentSkill::class, 'character_id');
    }

    public function senjutsuSkills()
    {
        return $this->hasMany(CharacterSenjutsuSkill::class, 'character_id');
    }

    public function getRelationForColumn(string $column)
    {
        switch ($column) {
            case 'char_skills': return $this->skills();
            case 'char_talent_skills': return $this->talentSkills();
            case 'char_senjutsu_skills': return $this->senjutsuSkills();
            default: return $this->items()->where('item_type', $column);
        }
    }

    public function getInventoryString(string $column): string
    {
        $items = $this->getRelationForColumn($column)->get();
        if ($items->isEmpty()) return "";

        $always_qty_columns = [
            'char_weapons', 'char_back_items', 'char_accessories', 
            'char_sets', 'char_essentials', 'char_materials', 
            'char_senjutsu_skills', 'char_talent_skills'
        ];

        $id_col = in_array($column, ['char_skills', 'char_talent_skills', 'char_senjutsu_skills']) ? 'skill_id' : 'item_id';

        $parts = [];
        foreach ($items as $item) {
            if ($item->quantity > 1 || in_array($column, $always_qty_columns)) {
                $parts[] = "{$item->$id_col}:{$item->quantity}";
            } else {
                $parts[] = $item->$id_col;
            }
        }
        return implode(',', $parts);
    }

    public function setInventoryString(string $column, string $value): void
    {
        $relation = $this->getRelationForColumn($column);
        $relation->delete(); // Clear existing

        $id_col = in_array($column, ['char_skills', 'char_talent_skills', 'char_senjutsu_skills']) ? 'skill_id' : 'item_id';

        $parts = explode(',', $value);
        foreach ($parts as $part) {
            if (empty($part)) continue;
            
            $data = $id_col === 'item_id' ? ['item_type' => $column] : [];

            if (strpos($part, ':') !== false) {
                list($ident, $qty) = explode(':', $part);
            } else {
                $ident = $part;
                $qty = 1;
            }

            if (str_starts_with($ident, 'hair_') || str_starts_with($ident, 'set_')) {
                if (!preg_match('/_[01]$/', $ident)) {
                    $ident .= '_' . $this->gender;
                }
            }

            $data[$id_col] = $ident;
            $data['quantity'] = (int)$qty;
            
            // Relational create
            $this->getRelationForColumn($column)->create($data);
        }
    }

    // Accessors for ActionScript compatibility (e.g., $char->char_weapons)
    public function getCharWeaponsAttribute() { return $this->getInventoryString('char_weapons'); }
    public function getCharPetWeaponsAttribute() { return $this->getInventoryString('char_pet_weapons'); }
    public function getCharBackItemsAttribute() { return $this->getInventoryString('char_back_items'); }
    public function getCharPetBackItemsAttribute() { return $this->getInventoryString('char_pet_back_items'); }
    public function getCharAccessoriesAttribute() { return $this->getInventoryString('char_accessories'); }
    public function getCharSetsAttribute() { return $this->getInventoryString('char_sets'); }
    public function getCharHairsAttribute() { return $this->getInventoryString('char_hairs'); }
    public function getCharSkillsAttribute() { return $this->getInventoryString('char_skills'); }
    public function getCharMaterialsAttribute() { return $this->getInventoryString('char_materials'); }
    public function getCharEssentialsAttribute() { return $this->getInventoryString('char_essentials'); }
    public function getCharTalentSkillsAttribute() { return $this->getInventoryString('char_talent_skills'); }
    public function getCharSenjutsuSkillsAttribute() { return $this->getInventoryString('char_senjutsu_skills'); }

    // Mutators that allow old setter logic ($char->char_skills = 'skill_1') to write directly to DB
    public function setCharWeaponsAttribute($value) { $this->setInventoryString('char_weapons', $value); }
    public function setCharPetWeaponsAttribute($value) { $this->setInventoryString('char_pet_weapons', $value); }
    public function setCharBackItemsAttribute($value) { $this->setInventoryString('char_back_items', $value); }
    public function setCharPetBackItemsAttribute($value) { $this->setInventoryString('char_pet_back_items', $value); }
    public function setCharAccessoriesAttribute($value) { $this->setInventoryString('char_accessories', $value); }
    public function setCharSetsAttribute($value) { $this->setInventoryString('char_sets', $value); }
    public function setCharHairsAttribute($value) { $this->setInventoryString('char_hairs', $value); }
    public function setCharSkillsAttribute($value) { $this->setInventoryString('char_skills', $value); }
    public function setCharMaterialsAttribute($value) { $this->setInventoryString('char_materials', $value); }
    public function setCharEssentialsAttribute($value) { $this->setInventoryString('char_essentials', $value); }
    public function setCharTalentSkillsAttribute($value) { $this->setInventoryString('char_talent_skills', $value); }
    public function setCharSenjutsuSkillsAttribute($value) { $this->setInventoryString('char_senjutsu_skills', $value); }

    public function getInventoryArray(string $column): array
    {
        $items = $this->getRelationForColumn($column)->get();
        $res = [];
        $id_col = in_array($column, ['char_skills', 'char_talent_skills', 'char_senjutsu_skills']) ? 'skill_id' : 'item_id';

        foreach ($items as $item) {
            $res[$item->$id_col] = $item->quantity;
        }
        return $res;
    }

    public function addToInventory(string $column, string $id, int $qty = 1): void
    {
        // Auto-append gender to sets and hairstyles if they don't already have a gender suffix
        if (str_starts_with($id, 'hair_') || str_starts_with($id, 'set_')) {
            if (!preg_match('/_[01]$/', $id)) {
                $id .= '_' . $this->gender;
            }
        }

        $id_col = in_array($column, ['char_skills', 'char_talent_skills', 'char_senjutsu_skills']) ? 'skill_id' : 'item_id';
        $attributes = [$id_col => $id];
        if ($id_col === 'item_id') $attributes['item_type'] = $column;

        $item = $this->getRelationForColumn($column)->firstOrNew($attributes);
        
        $item->quantity = ($item->exists ? $item->quantity : 0) + $qty;
        $item->save();
    }

    public function removeFromInventory(string $column, string $id, int $qty = 1): bool
    {
        $id_col = in_array($column, ['char_skills', 'char_talent_skills', 'char_senjutsu_skills']) ? 'skill_id' : 'item_id';
        $item = $this->getRelationForColumn($column)->where($id_col, $id)->first();
        if (!$item || $item->quantity < $qty) {
            return false;
        }
        
        $item->quantity -= $qty;
        if ($item->quantity <= 0) {
            $item->delete();
        } else {
            $item->save();
        }
        return true;
    }
}
