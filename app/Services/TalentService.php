<?php

namespace App\Services;
class TalentService
{
    private $talent_data = [
        'snow'         => ['price' => 1500, 'is_token' => true,  'is_emblem_plus' => true],
        'gale'         => ['price' => 1500, 'is_token' => true,  'is_emblem'      => true],
        'underworld'   => ['price' => 2000, 'is_token' => true,  'is_emblem_plus' => true],
        'cataclysm'    => ['price' => 1000, 'is_token' => true,  'is_emblem_plus' => true],
        'puppeteering' => ['price' => 1500, 'is_token' => true,  'is_emblem'      => true],
        'sand'         => ['price' => 1000, 'is_token' => true,  'is_emblem'      => true],
        'eternalflame' => ['price' => 1000, 'is_token' => true,  'is_emblem'      => true],
        'eoa'          => ['price' => 1000, 'is_token' => true,  'is_emblem'      => true],
        'saint'        => ['price' => 2500, 'is_token' => true],
        'insect'       => ['price' => 3000000, 'is_token' => false],
        'eightext'     => ['price' => 400,  'is_token' => true],
        'eom'          => ['price' => 400,  'is_token' => true,  'is_emblem'      => true],
        'de'           => ['price' => 500000,  'is_token' => false],
        'dp'           => ['price' => 500000,  'is_token' => false],
        'orochi'       => ['price' => 400,  'is_token' => true],
        'silhouette'   => ['price' => 400,  'is_token' => true,  'is_emblem'      => true],
        'forest'       => ['price' => 1000000, 'is_token' => false],
        'demon'        => ['price' => 1000000, 'is_token' => false],
        'lava'         => ['price' => 1000000, 'is_token' => false],
        'crystal'      => ['price' => 1000000, 'is_token' => false],
        'vampiric'     => ['price' => 400,  'is_token' => true],
        'bones'        => ['price' => 800,  'is_token' => true,  'is_emblem'      => true],
    ];

    private $talent_first_skill = [
        'underworld'   => 'skill_1036',
        'cataclysm'    => 'skill_1084',
        'eoa'          => 'skill_1030',
        'eom'          => 'skill_1018',
        'eightext'     => 'skill_1000',
        'orochi'       => 'skill_1024',
        'de'           => 'skill_1006',
        'dp'           => 'skill_1012',
        'saint'        => 'skill_1069',
        'insect'       => 'skill_1075',
        'snow'         => 'skill_1099',
        'gale'         => 'skill_1096',
        'puppeteering' => 'skill_1093',
        'sand'         => 'skill_1090',
        'bones'        => 'skill_1083',
        'silhouette'   => 'skill_1057',
        'vampiric'     => 'skill_1066',
        'demon'        => 'skill_1051',
        'forest'       => 'skill_1060',
        'lava'         => 'skill_1054',
        'crystal'      => 'skill_1063',
    ];

    private function getSkillRequirements($level)
    {
        $reqs = [
            1 => ['tp' => 5,   'tokens' => 5,   'essentials' => 1, 'max_tokens' => 1780],
            2 => ['tp' => 10,  'tokens' => 10,  'essentials' => 1, 'max_tokens' => 1775],
            3 => ['tp' => 25,  'tokens' => 20,  'essentials' => 1, 'max_tokens' => 1765],
            4 => ['tp' => 50,  'tokens' => 35,  'essentials' => 1, 'max_tokens' => 1745],
            5 => ['tp' => 100, 'tokens' => 75,  'essentials' => 1, 'max_tokens' => 1710],
            6 => ['tp' => 200, 'tokens' => 135, 'essentials' => 1, 'max_tokens' => 1635],
            7 => ['tp' => 300, 'tokens' => 200, 'essentials' => 1, 'max_tokens' => 1500],
            8 => ['tp' => 450, 'tokens' => 300, 'essentials' => 1, 'max_tokens' => 1300],
            9 => ['tp' => 600, 'tokens' => 450, 'essentials' => 1, 'max_tokens' => 1000],
            10 => ['tp' => 800, 'tokens' => 550, 'essentials' => 1, 'max_tokens' => 550],
        ];
        return $reqs[$level] ?? null;
    }

    /**
     * getTalentSkills — returns the character's discovered talent skills.
     * Client reads: param1.data  (array of { item_id, item_level })
     */
    public function getTalentSkills($char_id, $sessionkey): array
    {
        $char = \App\Models\Character::find((int) $char_id);
        if (!$char) {
            return ['status' => 0, 'result' => 'Character not found.'];
        }

        $skills = [];
        if ($char->char_talent_skills) {
            // Format: skill_id:level,skill_id:level
            $pairs = explode(',', $char->char_talent_skills);
            foreach ($pairs as $pair) {
                if (strpos($pair, ':') !== false) {
                    list($id, $lvl) = explode(':', $pair);
                    $skills[] = ['item_id' => $id, 'item_level' => (int)$lvl];
                }
            }
        }

        return [
            'status' => 1,
            'error'  => 0,
            'data'   => $skills,
        ];
    }

    /**
     * discoverTalent — allows purchasing a talent.
     */
    public function discoverTalent($char_id, $sessionkey, $type, $talent_id): array
    {
        $char = \App\Models\Character::find((int) $char_id);
        if (!$char) {
            return ['status' => 0, 'result' => 'Character not found.'];
        }

        $user = $char->user;
        if (!$user) {
            return ['status' => 0, 'result' => 'User not found.'];
        }

        $talent = $this->talent_data[$talent_id] ?? null;
        if (!$talent) {
            return ['status' => 0, 'result' => "Talent '{$talent_id}' info missing."];
        }

        // Require Emblem/Plus if specified
        if (!empty($talent['is_emblem_plus']) && $user->account_type < 2) {
            return ['status' => 0, 'result' => 'This talent requires Emblem Plus.'];
        }
        if (!empty($talent['is_emblem']) && $user->account_type < 1) {
            return ['status' => 0, 'result' => 'This talent requires Emblem.'];
        }

        // Determine which slot to fill
        $newt = 0;
        if ($type === 'Extreme') {
            if ($char->char_talent_1) {
                return ['status' => 0, 'result' => 'You already have an Extreme talent.'];
            }
            $newt = 1;
        } else {
            // Secret talents go into slot 2 or 3
            if (!$char->char_talent_2) {
                if ($char->level < 50) {
                    return ['status' => 0, 'result' => 'Level 50 required for First Secret slot.'];
                }
                $newt = 2;
            } elseif (!$char->char_talent_3) {
                if ($char->level < 60 || $char->rank < 6) {
                    return ['status' => 0, 'result' => 'Level 60 and Rank 6 required for Second Secret slot.'];
                }
                $newt = 3;
            } else {
                return ['status' => 0, 'result' => 'No available Secret talent slots.'];
            }
        }

        // Deduct price
        $price = $talent['price'];
        if ($talent['is_token']) {
            if ($user->tokens < $price) {
                return ['status' => 0, 'result' => 'Not enough Tokens.'];
            }
            $user->tokens -= $price;
        } else {
            $gold = (int) $char->gold;
            if ($gold < $price) {
                return ['status' => 0, 'result' => 'Not enough Gold.'];
            }
            $char->gold = $gold - $price;
        }

        // Save talent to character slot
        $slot_name = "char_talent_{$newt}";
        $char->$slot_name = (string) $talent_id;
        
        // Also add the first skill of the talent at level 1
        $first_skill = $this->talent_first_skill[$talent_id] ?? null;
        if ($first_skill) {
            $skills_raw = $char->char_talent_skills ? explode(',', $char->char_talent_skills) : [];
            $found = false;
            foreach ($skills_raw as $pair) {
                if (strpos($pair, $first_skill . ':') === 0) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $skills_raw[] = "{$first_skill}:1";
                $char->char_talent_skills = implode(',', $skills_raw);
            }
        }
        
        $char->save();
        $user->save();

        return [
            'status' => 1,
            'newt'   => $newt,
            'tokens' => (int) $user->tokens,
            'golds'  => (int) $char->gold
        ];
    }

    public function newUpgradeSkill($char_id, $sessionkey, $skill_id, $btn_name): array
    {
        $char = \App\Models\Character::find((int) $char_id);
        if (!$char) {
            return ['status' => 0, 'result' => 'Character not found.'];
        }

        $user = $char->user;
        if (!$user) {
            return ['status' => 0, 'result' => 'User not found.'];
        }

        // Parse skills
        $skills = [];
        if ($char->char_talent_skills) {
            $pairs = explode(',', $char->char_talent_skills);
            foreach ($pairs as $pair) {
                if (strpos($pair, ':') !== false) {
                    list($id, $lvl) = explode(':', $pair);
                    $skills[$id] = (int)$lvl;
                }
            }
        }

        $current_lvl = $skills[$skill_id] ?? 0;
        $next_lvl = $current_lvl + 1;

        if ($next_lvl > 10) {
            return ['status' => 0, 'result' => 'Skill already at maximum level.'];
        }

        $req = $this->getSkillRequirements($next_lvl);
        if (!$req) {
            return ['status' => 0, 'result' => 'Requirements for level ' . $next_lvl . ' not found.'];
        }

        $costType = '';
        $amount = 0;

        switch ($btn_name) {
            case 'btn_upgrade_0': // TP
                $costType = 'tp';
                $amount = $req['tp'];
                if ($char->tp < $amount) {
                    return ['status' => 0, 'result' => 'Not enough TP.'];
                }
                $char->tp -= $amount;
                break;

            case 'btn_upgrade_1': // Tokens
                $costType = 'token';
                $amount = $req['tokens'];
                if ($user->tokens < $amount) {
                    return ['status' => 0, 'result' => 'Not enough Tokens.'];
                }
                $user->tokens -= $amount;
                break;

            case 'btn_upgrade_2': // Essentials
                $costType = 'essential';
                $amount = $req['essentials'];
                // Assume char_essentials is a comma-separated list or check if we have a helper
                // For now, let's just deduct it if we can find it in char_essentials string
                $essentials = $char->getInventoryArray('char_essentials');
                $essential_id = 'essential_125';
                if (($essentials[$essential_id] ?? 0) < $amount) {
                    return ['status' => 0, 'result' => 'Not enough Essentials.'];
                }
                $char->removeFromInventory('char_essentials', $essential_id, $amount);
                break;

            case 'btn_upgrade_3': // Max level/tokens? max_tokens
                $costType = 'token';
                $amount = $req['max_tokens'];
                if ($user->tokens < $amount) {
                    return ['status' => 0, 'result' => 'Not enough Tokens.'];
                }
                $user->tokens -= $amount;
                // Maybe this upgrades to level 10 instantly? 
                // In AS it just seems like another payment option for "next level".
                break;

            default:
                return ['status' => 0, 'result' => 'Invalid upgrade option.'];
        }

        // Update skill level
        if ($costType === 'token' && $amount != 5) {
            $skills[$skill_id] = 10;
        } else {
            $skills[$skill_id] = $next_lvl;
        }
        
        // Serialize back
        $new_skills_str = [];
        foreach ($skills as $id => $lvl) {
            $new_skills_str[] = "{$id}:{$lvl}";
        }
        $char->char_talent_skills = implode(',', $new_skills_str);

        $char->save();
        $user->save();

        return [
            'status' => 1,
            'data'   => [
                'costType' => $costType,
                'amount'   => $amount
            ]
        ];
    }

    use \App\Traits\SessionValidator;

    public function useTalentPointPill($char_id, $sessionkey, $item_id, $qty): array
    {
        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof \App\Models\Character)) return $char;

        $qty = (int)$qty;
        if (!$char->hasInInventory('char_essentials', $item_id, $qty)) {
            return ['status' => 0, 'result' => 'You do not have enough items!'];
        }

        $tp_values = [
            'essential_21' => 10,
            'essential_22' => 50,
            'essential_23' => 100,
            'essential_168' => 10,
            'essential_169' => 50,
            'essential_170' => 100,
        ];

        $tp_per_pill = $tp_values[$item_id] ?? 10;
        $total_tp = $tp_per_pill * $qty;

        $char->tp += $total_tp;
        $char->removeFromInventory('char_essentials', $item_id, $qty);
        $char->save();

        return [
            'status'         => 1,
            'essential_used' => $item_id,
            'total'          => $qty,
            'tp_got'         => $total_tp,
            'result'         => "Successfully used {$qty} pills and got {$total_tp} TP!"
        ];
    }
}
