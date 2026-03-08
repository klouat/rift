<?php

namespace App\Services;

class AdvancedAcademyService
{
    use \App\Traits\SessionValidator;
    // Ordered skill group names per element (used by "skill_names" request)
    private const SKILL_NAMES = [
        'wind'     => ['evasion','blade_of_wind','wind_peace','dance_of_fujin','breakthrough','fujin_storm','shuriken_gourd','storm_boomerang','tornado_shield'],
        'fire'     => ['fire_power','hell_fire','fire_energy','rage','phoenix','yama','flame_armor','devouring_flames','fire_titan'],
        'thunder'  => ['charge','flash','bundle','armor','boost','narukami','explosion','chasing','armanent'],
        'earth'    => ['golem','absorb','rocks','embrace','gaunt','golem_great','slurry_coat','monolith','eagle'],
        'water'    => ['renewal','bundle','prison','shield','shark','strong_arm','aqua_mirror','aqua_cannon','pool'],
        'taijutsu' => ['black_friday_punch','wild_bease_spear','tremor_combo','dynamic_fiery_fist','battle_spirit_awakening','spartan','amazing_home','taka','brutal_axe','giant_punch','schoolbag'],
        'genjutsu' => ['fat_woman','sexy_girl','feather_illusion','battle_soul','easter_egg','three_rods','pandoras_box','tog','spiritual','yata','batalizord','black_lightning','aetheric','christmas_box','grasp','bijuu_bomb','monarch'],
        'legendary_taijutsu' => ['skill_1810','skill_1811','skill_1812','skill_1322','skill_1813','skill_1814','skill_1815','skill_1816','skill_1817','skill_1818','skill_1819','skill_1820','skill_1821','skill_1835','skill_1836','skill_1837','skill_1838'],
        'legendary_genjutsu' => ['skill_1801','skill_1802','skill_1803','skill_1804','skill_1805','skill_1806','skill_1807','skill_1808','skill_1809','skill_1822','skill_1823','skill_1824','skill_1825','skill_1826','skill_1827','skill_1828','skill_1829','skill_1830','skill_1831','skill_1832','skill_1833','skill_1834','skill_1839','skill_1840','skill_1841'],
    ];

    // Full skill upgrade chains per element group
    private const SKILL_TREE = [
        'wind' => [
            'evasion'        => ['skill_39','skill_661','skill_662','skill_663','skill_664','skill_665','skill_666','skill_721','skill_822','skill_823'],
            'blade_of_wind'  => ['skill_85','skill_667','skill_668','skill_669','skill_670','skill_671','skill_722','skill_824','skill_825'],
            'wind_peace'     => ['skill_161','skill_672','skill_673','skill_674','skill_675','skill_723','skill_826','skill_827'],
            'dance_of_fujin' => ['skill_151','skill_676','skill_677','skill_678','skill_724','skill_828','skill_829'],
            'breakthrough'   => ['skill_285','skill_679','skill_680','skill_725','skill_830','skill_831'],
            'fujin_storm'    => ['skill_704','skill_726','skill_727','skill_832','skill_833'],
            'shuriken_gourd' => ['skill_742','skill_743','skill_834','skill_835'],
            'storm_boomerang'=> ['skill_744','skill_836','skill_837'],
            'tornado_shield' => ['skill_780','skill_838'],
        ],
        'fire' => [
            'fire_power'      => ['skill_36','skill_601','skill_602','skill_603','skill_604','skill_605','skill_606','skill_706','skill_788','skill_789'],
            'hell_fire'       => ['skill_86','skill_607','skill_608','skill_609','skill_610','skill_611','skill_707','skill_790','skill_791'],
            'fire_energy'     => ['skill_162','skill_612','skill_613','skill_614','skill_615','skill_708','skill_792','skill_793'],
            'rage'            => ['skill_152','skill_616','skill_617','skill_618','skill_709','skill_794','skill_795'],
            'phoenix'         => ['skill_234','skill_619','skill_620','skill_710','skill_796','skill_797'],
            'yama'            => ['skill_701','skill_711','skill_712','skill_798','skill_799'],
            'flame_armor'     => ['skill_751','skill_752','skill_800','skill_801'],
            'devouring_flames'=> ['skill_753','skill_802','skill_803'],
            'fire_titan'      => ['skill_778','skill_804'],
        ],
        'thunder' => [
            'charge'   => ['skill_35','skill_681','skill_682','skill_683','skill_684','skill_685','skill_686','skill_713','skill_805','skill_806'],
            'flash'    => ['skill_87','skill_687','skill_688','skill_689','skill_690','skill_691','skill_714','skill_807','skill_808'],
            'bundle'   => ['skill_163','skill_692','skill_693','skill_694','skill_695','skill_715','skill_809','skill_810'],
            'armor'    => ['skill_153','skill_696','skill_697','skill_698','skill_716','skill_811','skill_812'],
            'boost'    => ['skill_220','skill_699','skill_717','skill_718','skill_813','skill_814'],
            'narukami' => ['skill_705','skill_719','skill_720','skill_815','skill_816'],
            'explosion'=> ['skill_754','skill_755','skill_817','skill_818'],
            'chasing'  => ['skill_756','skill_819','skill_820'],
            'armanent' => ['skill_782','skill_821'],
        ],
        'earth' => [
            'golem'      => ['skill_59','skill_621','skill_622','skill_623','skill_624','skill_625','skill_626','skill_735','skill_839','skill_840'],
            'absorb'     => ['skill_88','skill_627','skill_628','skill_629','skill_630','skill_631','skill_736','skill_841','skill_842'],
            'rocks'      => ['skill_164','skill_632','skill_633','skill_634','skill_635','skill_737','skill_843','skill_844'],
            'embrace'    => ['skill_154','skill_636','skill_637','skill_638','skill_738','skill_845','skill_846'],
            'gaunt'      => ['skill_251','skill_639','skill_640','skill_739','skill_847','skill_848'],
            'golem_great'=> ['skill_703','skill_740','skill_741','skill_849','skill_850'],
            'slurry_coat'=> ['skill_748','skill_749','skill_851','skill_852'],
            'monolith'   => ['skill_750','skill_853','skill_854'],
            'eagle'      => ['skill_784','skill_855'],
        ],
        'water' => [
            'renewal'    => ['skill_60','skill_641','skill_642','skill_643','skill_644','skill_645','skill_646','skill_728','skill_856','skill_857'],
            'bundle'     => ['skill_89','skill_647','skill_648','skill_649','skill_650','skill_651','skill_729','skill_858','skill_859'],
            'prison'     => ['skill_165','skill_652','skill_653','skill_654','skill_655','skill_730','skill_860','skill_861'],
            'shield'     => ['skill_122','skill_656','skill_657','skill_658','skill_731','skill_862','skill_863'],
            'shark'      => ['skill_268','skill_659','skill_660','skill_732','skill_864','skill_865'],
            'strong_arm' => ['skill_702','skill_733','skill_734','skill_866','skill_867'],
            'aqua_mirror'=> ['skill_745','skill_746','skill_869','skill_870'],
            'aqua_cannon'=> ['skill_747','skill_871','skill_872'],
            'pool'       => ['skill_786','skill_873'],
        ],
        'taijutsu' => [
            'black_friday_punch'      => ['skill_385','skill_1725','skill_1726','skill_1727','skill_1728','skill_1729'],
            'wild_bease_spear'        => ['skill_386','skill_1730','skill_1731','skill_1732','skill_1733','skill_1734'],
            'tremor_combo'            => ['skill_334','skill_1735','skill_1736','skill_1737','skill_1738'],
            'dynamic_fiery_fist'      => ['skill_325','skill_1739','skill_1740','skill_1741','skill_1742'],
            'battle_spirit_awakening' => ['skill_997','skill_1743','skill_1744'],
            'spartan'                 => ['skill_1302','skill_1810','skill_1811','skill_1812'],
            'amazing_home'            => ['skill_1321','skill_1322','skill_1813','skill_1814','skill_1815'],
            'taka'                    => ['skill_376','skill_1816','skill_1817','skill_1818'],
            'brutal_axe'              => ['skill_432','skill_1819','skill_1820','skill_1821'],
            'giant_punch'             => ['skill_1327','skill_1835','skill_1836'],
            'schoolbag'               => ['skill_1320','skill_1837','skill_1838'],
        ],
        'genjutsu' => [
            'fat_woman'      => ['skill_03','skill_1701','skill_1702','skill_1703','skill_1704','skill_1705'],
            'sexy_girl'      => ['skill_04','skill_1706','skill_1707','skill_1708','skill_1709','skill_1710'],
            'feather_illusion'=> ['skill_57','skill_1711','skill_1712','skill_1713','skill_1714','skill_1715'],
            'battle_soul'    => ['skill_326','skill_1716','skill_1717','skill_1718','skill_1719','skill_1720'],
            'easter_egg'     => ['skill_347','skill_1721','skill_1722','skill_1723','skill_1724'],
            'three_rods'     => ['skill_348','skill_1745','skill_1746','skill_1747','skill_1748'],
            'pandoras_box'   => ['skill_311','skill_312','skill_313','skill_314','skill_1749'],
            'tog'            => ['skill_344','skill_1801','skill_1802'],
            'spiritual'      => ['skill_912','skill_1839','skill_1840','skill_1841'],
            'yata'           => ['skill_1205','skill_1803','skill_1804'],
            'batalizord'     => ['skill_407','skill_1805','skill_1806','skill_1807'],
            'black_lightning' => ['skill_928','skill_1808','skill_1809'],
            'aetheric'       => ['skill_917','skill_1822','skill_1823'],
            'christmas_box'  => ['skill_946','skill_1824','skill_1825','skill_1826'],
            'grasp'          => ['skill_961','skill_1827','skill_1828'],
            'bijuu_bomb'     => ['skill_331','skill_1829','skill_1830','skill_1831'],
            'monarch'        => ['skill_1295','skill_1832','skill_1833','skill_1834'],
        ],
    ];

    public function executeService($action, $params = [])
    {
        switch ($action) {
            case 'getSkills':
                return $this->get_skills($params);
            case 'newUpgradeSkill':
                return $this->new_upgrade_skill($params);
            default:
                return ['status' => 1];
        }
    }

    /**
     * With "skill_names" flag → ordered key list per element (flat arrays of group names).
     * Without flag → full skill tree (element → group → upgrade chain array).
     */
    private function get_skills(array $params): array
    {
        $is_names_request = isset($params[2]) && $params[2] === 'skill_names';

        if ($is_names_request) {
            return self::SKILL_NAMES;
        }

        // Return full tree as associative objects (stdClass so AMF encodes as Object, not Array)
        $result = [];
        foreach (self::SKILL_TREE as $element => $groups) {
            $obj = new \stdClass();
            foreach ($groups as $group_name => $skill_ids) {
                $obj->$group_name = $skill_ids;
            }
            $result[$element] = $obj;
        }

        return $result;
    }

    private function new_upgrade_skill(array $params): array
    {
        $char_id          = (int)$params[0];
        $sessionkey       = $params[1];
        $element_type     = $params[2]; // e.g., "wind"
        $group_name       = $params[3]; // e.g., "evasion"
        $current_skill_id = $params[4];
        $next_skill_id    = $params[5];
        $current_level    = (int)$params[6]; // Index of current_skill_id in the chain
        $forge_type       = $params[7]; // btn_upgrade, forgeBtn, instaForgeBtn

        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof \App\Models\Character)) return $char;

        // 1. Verify group and sequence
        $tree = self::SKILL_TREE[$element_type][$group_name] ?? null;
        if (!$tree) {
            return ['status' => 0, 'result' => 'Invalid skill group.'];
        }

        // Verify current_skill_id is at current_level index
        if (($tree[$current_level] ?? null) !== $current_skill_id) {
            return ['status' => 0, 'result' => 'Skill sequence mismatch.'];
        }

        // next_skill_id should be at current_level + 1
        $next_idx = $current_level + 1;
        if (($tree[$next_idx] ?? null) !== $next_skill_id) {
            return ['status' => 0, 'result' => 'Invalid next skill.'];
        }

        // 2. Identify the target skill and its properties
        // We'll estimate token price based on the level index (next_idx)
        // Level 1: 50, Level 2: 100, Level 3: 150...
        $token_price = 50 * $next_idx;
        if (str_contains($element_type, 'legendary')) {
            $token_price *= 2; // Legendary skills cost double
        }

        $gold_cost = 10000 * pow(2, $current_level);
        
        $element_map = [
            'wind' => 1, 'fire' => 2, 'thunder' => 3, 'earth' => 4, 'water' => 5,
            'taijutsu' => 6, 'genjutsu' => 7, 'legendary_taijutsu' => 8, 'legendary_genjutsu' => 9
        ];
        $elt_idx = $element_map[$element_type] ?? 1;
        
        $pill_id     = 'essential_9' . $elt_idx;
        $pill_amount = (int)floor($token_price / 50) + 1;

        // 3. Process the upgrade based on forge type
        if ($forge_type === 'btn_upgrade') {
            if ($char->user->tokens < $token_price) {
                return ['status' => 2, 'result' => 'Not enough tokens.'];
            }
            $char->user->tokens -= $token_price;
            $char->user->save();
        } 
        elseif ($forge_type === 'forgeBtn') {
            if ($char->gold < $gold_cost) {
                return ['status' => 2, 'result' => 'Not enough gold.'];
            }
            if (!$char->hasInInventory('char_essentials', $pill_id, $pill_amount)) {
                return ['status' => 2, 'result' => 'Not enough ' . $pill_id . ' pills.'];
            }
            $char->gold -= $gold_cost;
            $char->removeFromInventory('char_essentials', $pill_id, $pill_amount);
        } 
        elseif ($forge_type === 'instaForgeBtn') {
            // Check if user has free daily skill reward (element_learned == 0)
            $today = now()->toDateString();
            $can_free_upgrade = ($char->ed_skills_last_claim !== $today);

            if ($can_free_upgrade) {
                $char->ed_skills_last_claim = $today;
            } else {
                // Otherwise use Ancient Scroll
                if (!$char->removeFromInventory('char_essentials', 'essential_07', 1)) {
                    return ['status' => 2, 'result' => 'No free upgrades left today and no Secret Scroll of Wisdom in inventory.'];
                }
            }
        } else {
            return ['status' => 0, 'result' => 'Invalid upgrade method.'];
        }

        // 4. Update skills
        // Remove old skill, add new skill
        $char->removeFromInventory('char_skills', $current_skill_id);
        $char->addToInventory('char_skills', $next_skill_id);
        
        // Also update equipped skills if it was equipped
        $equipped = explode(',', $char->equipped_skills);
        if (($key = array_search($current_skill_id, $equipped)) !== false) {
            $equipped[$key] = $next_skill_id;
            $char->equipped_skills = implode(',', $equipped);
        }
        
        $char->save();

        return [
            'status'        => 1,
            'result'        => 'Skill upgraded successful!',
            'cost'          => $token_price,
            'gold_cost'     => $gold_cost,
            'pill_amt_data' => [$pill_id, $pill_amount],
            'lastSkillId'   => $next_skill_id,
            'skills'        => $char->equipped_skills // AS uses this to update both set and equipped
        ];
    }
}
