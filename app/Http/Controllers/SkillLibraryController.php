<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SkillLibraryController extends Controller
{
    /**
     * Returns the full skill library as JSON.
     * The client (SkillLibrary.as) parses this as JSON and populates loaded_effects.
     */
    public function index()
    {
        $skills = [
            'skill_01' => [
                'item_id'           => 'skill_01',
                'skill_id'          => 'skill_01',
                'skill_type'        => '3', // Thunder
                'skill_name'        => 'Chidori',
                'skill_level'       => 1,
                'skill_description' => 'A concentrated lightning strike.',
                'skill_damage'      => 45,
                'skill_cp_cost'     => 50,
                'skill_cooldown'    => 2,
                'skill_target'      => 'Single',
                'skill_premium'     => false,
                'skill_buyable'     => true,
                'skill_price_gold'  => 500,
                'skill_price_tokens'=> 0,
                'effects'           => [
                    'skill_id'          => 'skill_01',
                    'skill_type'        => '3',
                    'skill_name'        => 'Chidori',
                    'skill_level'       => 1,
                    'skill_description' => 'A concentrated lightning strike.',
                    'skill_damage'      => 45,
                    'skill_cp_cost'     => 50,
                    'skill_cooldown'    => 2,
                    'skill_target'      => 'Single',
                    'skill_premium'     => false,
                    'skill_buyable'     => true,
                    'skill_price_gold'  => 500,
                    'skill_price_tokens'=> 0,
                ]
            ],
            'skill_13' => [
                'item_id'           => 'skill_13',
                'skill_id'          => 'skill_13',
                'skill_type'        => '1', // Wind
                'skill_name'        => 'Great Breach',
                'skill_level'       => 1,
                'skill_description' => 'A strong gust of wind.',
                'skill_damage'      => 30,
                'skill_cp_cost'     => 30,
                'skill_cooldown'    => 0,
                'skill_target'      => 'Single',
                'skill_premium'     => false,
                'skill_buyable'     => true,
                'skill_price_gold'  => 200,
                'skill_price_tokens'=> 0,
                'effects'           => [
                    'skill_id'          => 'skill_13',
                    'skill_type'        => '1',
                    'skill_name'        => 'Great Breach',
                    'skill_level'       => 1,
                    'skill_description' => 'A strong gust of wind.',
                    'skill_damage'      => 30,
                    'skill_cp_cost'     => 30,
                    'skill_cooldown'    => 0,
                    'skill_target'      => 'Single',
                    'skill_premium'     => false,
                    'skill_buyable'     => true,
                    'skill_price_gold'  => 200,
                    'skill_price_tokens'=> 0,
                ]
            ],
            // Add other skills as needed...
        ];

        return response()->json($skills);
    }
}
