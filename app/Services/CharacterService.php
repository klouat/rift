<?php

namespace App\Services;

use App\Models\User;
use App\Models\Character;

class CharacterService {

    public function characterRegister($data) {
        /**
         * ActionScript expects:
         * var _loc2_:Array = [
         *    Character.account_id, (0)
         *    CUCSG.hash(Character.sessionkey), (1)
         *    this["char_name"].text, (2)
         *    this.current_gender, (3)
         *    this.element, (4)
         *    this.selected_hair_style_color, (5)
         *    this.hair_num, (6)
         *    this.skin_color (7)
         * ];
         */

        if (!is_array($data) || count($data) < 8) {
            return ["status" => 0, "error" => "Invalid character data sent."];
        }

        $accountId = cloneVal($data[0]); // Make sure to decode properly if it's strangely formatted by Sabre
        $sessionkeyHash = $data[1]; // Ignored validation for now
        $charName = $data[2];
        $gender = $data[3];
        $element = $data[4];
        $hairStyleColor = $data[5];
        $hairNum = $data[6];
        $skinColor = $data[7];

        if (Character::where('name', $charName)->exists()) {
            return ["status" => 0, "error" => "Character name is already taken!"];
        }

        $hair_id  = 'hair_01_' . ($gender == 0 ? '0' : '1');
        $set_id   = 'set_01_' . ($gender == 0 ? '0' : '1');
        $face_id  = 'face_01_' . ($gender == 0 ? '0' : '1');

        $char = Character::create([
            'account_id'        => is_array($accountId) ? $accountId[0] : (int) $accountId,
            'name'              => (string) $charName,
            'gender'            => (int) $gender,
            'element'           => (int) $element,
            'hair_style_color'  => $hairStyleColor ?: '0|0',
            'hair_num'          => (int) $hairNum,
            'skin_color'        => $skinColor ?: 16173743,
            'level'             => 1,
            'gold'              => 0,
            'profile_pic'       => 1,
            'xp'                => 0,
            'rank'              => 1,
            'tp'                => 0,
            'element_2'         => 0,
            'element_3'         => 0,
            'element_4'         => 0,
            'element_5'         => 0,
            // Inventories
            'char_weapons'      => 'wpn_01:1',
            'char_back_items'   => 'back_01:1',
            'char_accessories'  => 'accessory_01:1',
            'char_sets'         => $set_id . ':1',
            'char_hairs'        => $hair_id,
            'char_skills'       => 'skill_13',
            // Equipped slots
            'equipped_weapon'   => 'wpn_01',
            'equipped_back_item'=> 'back_01',
            'equipped_accessory'=> 'accessory_01',
            'equipped_clothing' => $set_id,
            'equipped_hairstyle'=> $hair_id,
            'equipped_skills'   => 'skill_13',
            'char_talent_1'     => '',
            'char_talent_2'     => '',
            'char_talent_3'     => '',
            // Attributes
            'atrrib_wind'       => 0,
            'atrrib_fire'       => 0,
            'atrrib_lightning'  => 0,
            'atrrib_water'      => 0,
            'atrrib_earth'      => 0,
            'atrrib_free'       => 0 // Points usually given on level up.
        ]);

        return [
            "status"  => 1,
            "char_id" => $char->id,
            "tutorial" => false
        ];
    }

    public function getEmblemDailyRewards($char_id, $sessionkey): array
    {
        return [
            "status" => 1,
            "result" => ["element_learned" => 0],
        ];
    }

    /**
     * Called after every battle — client ignores the response entirely,
     * just needs it to not error. Returns boolean true.
     */
    public function unSetPartyControl($char_id, $sessionkey): bool
    {
        return true;
    }

    public function unRecruitTeammates($char_id, $sessionkey): array
    {
        return ['status' => 1];
    }

    /**
     * Persists the character's attribute point allocation.
     *
     * Args (UI_Profile_New.as:599):
     *   char_id, sessionkey, wind, fire, lightning, water, earth, free
     *
     * Client reads (onClose:609–616): status == 1 → closes panel.
     */
    public function setPoints($char_id, $sessionkey, $wind, $fire, $lightning, $water, $earth, $free): array
    {
        $updated = Character::where('id', (int) $char_id)->update([
            'atrrib_wind'      => (int) $wind,
            'atrrib_fire'      => (int) $fire,
            'atrrib_lightning' => (int) $lightning,
            'atrrib_water'     => (int) $water,
            'atrrib_earth'     => (int) $earth,
            'atrrib_free'      => (int) $free,
        ]);

        return ['status' => $updated !== false ? 1 : 0];
    }

    /**
     * Args: char_id, sessionkey, username, password, verification (int, ignored)
     *
     * Response statuses (CharacterDelete.as:69–81):
     *   1 → deleted, client loads LoginManager
     *   2 → wrong password
     */
    public function deleteCharacter($char_id, $sessionkey, $username, $password, $verification = 0): array
    {
        $user = \App\Models\User::where('username', $username)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            return ['status' => 2]; // wrong password
        }

        \App\Models\Character::where('id', (int) $char_id)
            ->where('account_id', $user->id)
            ->delete();

        return ['status' => 1];
    }

    /**
     * Persists the equipped skills (comma-separated string).
     */
    public function equipSkillSet($char_id, $sessionkey, $skills): array
    {
        $char = Character::find((int) $char_id);
        if ($char) {
            $char->equipped_skills = (string) $skills;
            $char->save();
            return ['status' => 1];
        }
        return ['status' => 0];
    }

    /**
     * Persists the entire equipped set (UI_Gear_New.as:1942, 1965).
     */
    public function equipSet($char_id, $sessionkey, $weapon, $back_item, $set, $accessory, $hair, $hair_color, $skin_color): array
    {
        $char = Character::find((int) $char_id);
        if (!$char) {
            return ['status' => 0];
        }

        $char->equipped_weapon    = (string) $weapon;
        $char->equipped_back_item  = (string) $back_item;
        $char->equipped_clothing   = (string) $set;
        $char->equipped_accessory  = (string) $accessory;
        $char->equipped_hairstyle = (string) $hair;
        $char->hair_style_color    = (string) $hair_color;
        $char->skin_color          = (int) $skin_color;
        $char->save();

        return ['status' => 1];
    }

    /**
     * getAcademySkills — returns the list of skills available in the Academy.
     */
    public function getAcademySkills($sessionkey, $char_id): array
    {
        return [
            "skills" => [null, [], [], [], [], []],
            "wind_skills" => [
                "skill_13", "skill_16", "skill_06", "skill_17", "skill_29", "skill_30", "skill_45", "skill_46", "skill_52", "skill_39", 
                "skill_69", "skill_67", "skill_74", "skill_82", "skill_85", "skill_92", "skill_97", "skill_140", "skill_102", "skill_161", 
                "skill_146", "skill_123", "skill_111", "skill_118", "skill_151", "skill_156", "skill_203", "skill_171", "skill_285", "skill_126", 
                "skill_166", "skill_178", "skill_704", "skill_215", "skill_257", "skill_252", "skill_742", "skill_267", "skill_262", "skill_744", 
                "skill_757", "skill_758", "skill_780", "skill_759", "skill_760", "skill_781"
            ],
            "water_skills" => [
                "skill_09", "skill_24", "skill_22", "skill_33", "skill_23", "skill_58", "skill_25", "skill_40", "skill_51", "skill_56", 
                "skill_60", "skill_73", "skill_64", "skill_78", "skill_84", "skill_81", "skill_89", "skill_96", "skill_110", "skill_101", 
                "skill_144", "skill_106", "skill_198", "skill_165", "skill_150", "skill_196", "skill_133", "skill_116", "skill_197", "skill_121", 
                "skill_122", "skill_160", "skill_207", "skill_175", "skill_199", "skill_268", "skill_129", "skill_200", "skill_170", "skill_182", 
                "skill_201", "skill_702", "skill_261", "skill_256", "skill_202", "skill_219", "skill_745", "skill_266", "skill_272", "skill_208", 
                "skill_747", "skill_769", "skill_770", "skill_771", "skill_786", "skill_772", "skill_773", "skill_787"
            ],
            "fire_skills" => [
                "skill_10", "skill_20", "skill_28", "skill_11", "skill_19", "skill_18", "skill_21", "skill_47", "skill_53", "skill_36", 
                "skill_70", "skill_65", "skill_75", "skill_79", "skill_86", "skill_93", "skill_98", "skill_141", "skill_103", "skill_162", 
                "skill_147", "skill_130", "skill_112", "skill_117", "skill_152", "skill_157", "skill_204", "skill_172", "skill_234", "skill_127", 
                "skill_167", "skill_179", "skill_701", "skill_216", "skill_258", "skill_253", "skill_751", "skill_263", "skill_269", "skill_753", 
                "skill_774", "skill_775", "skill_778", "skill_776", "skill_777", "skill_779"
            ],
            "thunder_skills" => [
                "skill_01", "skill_38", "skill_14", "skill_26", "skill_05", "skill_15", "skill_27", "skill_49", "skill_54", "skill_35", 
                "skill_71", "skill_66", "skill_76", "skill_83", "skill_87", "skill_94", "skill_100", "skill_142", "skill_104", "skill_163", 
                "skill_148", "skill_135", "skill_113", "skill_119", "skill_153", "skill_158", "skill_205", "skill_173", "skill_220", "skill_124", 
                "skill_168", "skill_180", "skill_705", "skill_254", "skill_217", "skill_259", "skill_754", "skill_270", "skill_264", "skill_756", 
                "skill_761", "skill_762", "skill_782", "skill_763", "skill_764", "skill_783"
            ],
            "earth_skills" => [
                "skill_12", "skill_34", "skill_07", "skill_31", "skill_32", "skill_44", "skill_48", "skill_50", "skill_55", "skill_59", 
                "skill_72", "skill_63", "skill_77", "skill_80", "skill_88", "skill_95", "skill_99", "skill_143", "skill_105", "skill_164", 
                "skill_149", "skill_120", "skill_114", "skill_132", "skill_154", "skill_159", "skill_206", "skill_174", "skill_251", "skill_128", 
                "skill_169", "skill_181", "skill_703", "skill_218", "skill_260", "skill_265", "skill_748", "skill_255", "skill_271", "skill_750", 
                "skill_765", "skill_766", "skill_784", "skill_767", "skill_768", "skill_785"
            ],
            "taijutsu_skills" => [
                "skill_385", "skill_41", "skill_42", "skill_43", "skill_68", "skill_62", "skill_386", "skill_61", "skill_107", "skill_90", 
                "skill_334", "skill_139", "skill_108", "skill_189", "skill_325", "skill_191", "skill_186", "skill_190", "skill_389", "skill_194", 
                "skill_188", "skill_387", "skill_192", "skill_193", "skill_187", "skill_195", "skill_388", "skill_466", "skill_467", "skill_997", 
                "skill_468", "skill_469", "skill_470"
            ],
            "genjutsu_skills" => [
                "skill_03", "skill_04", "skill_57", "skill_326", "skill_347", "skill_348", "skill_184", "skill_311", "skill_393", "skill_365", 
                "skill_352", "skill_401", "skill_392"
            ],
            "ex_arr" => [
                "skill_39", "skill_85", "skill_161", "skill_151", "skill_285", "skill_704", "skill_742", "skill_744", "skill_780", "skill_36", 
                "skill_86", "skill_162", "skill_152", "skill_234", "skill_701", "skill_751", "skill_753", "skill_778", "skill_35", "skill_87", 
                "skill_163", "skill_153", "skill_220", "skill_705", "skill_754", "skill_756", "skill_782", "skill_59", "skill_88", "skill_164", 
                "skill_154", "skill_251", "skill_703", "skill_748", "skill_750", "skill_784", "skill_60", "skill_89", "skill_165", "skill_122", 
                "skill_268", "skill_702", "skill_745", "skill_747", "skill_786", "skill_325", "skill_334", "skill_385", "skill_386", "skill_387", 
                "skill_388", "skill_389", "skill_997", "skill_03", "skill_04", "skill_57", "skill_311", "skill_326", "skill_347", "skill_365", 
                "skill_367", "skill_348"
            ]
        ];
    }

    /**
     * buySkill — buys a skill from the Academy.
     */
    public function buySkill($sessionkey, $char_id, $skill_id): array
    {
        $char = Character::find((int) $char_id);
        if (!$char) {
            return ['status' => 0, 'error' => 'Character not found'];
        }

        $user = $char->user;

        // Check if skill already owned
        $skills_owned = $char->char_skills ? explode(',', $char->char_skills) : [];
        if (in_array($skill_id, $skills_owned)) {
            return ['status' => 2]; // You already own this skill.
        }

        // Price placeholder — originally this would be looked up from a DB or JSON
        $price = 500;

        if ($char->gold < $price) {
            return ['status' => 3]; // You do not have enough resources to learn this skill.
        }

        // Update character. Ninja Saga stores skills as a comma-separated string.
        $skills_owned[] = $skill_id;
        $char->char_skills = implode(',', $skills_owned);
        $char->gold -= $price;
        $char->save();

        return [
            'status' => 1,
            'data'   => [
                'character_gold'      => (string) $char->gold,
                'account_tokens'      => (int) ($user->tokens ?? 0),
                'character_element_1' => (int) ($char->element ?? 0),
                'character_element_2' => (int) ($char->element_2 ?? 0),
                'character_element_3' => (int) ($char->element_3 ?? 0),
                'character_element_4' => (int) ($char->element_4 ?? 0),
                'character_element_5' => (int) ($char->element_5 ?? 0)
            ]
        ];
    }
    /**
     * The client (UI_Skillset_New.as) uses these to populate the skill library.
     */
    public function getSkillData($char_id, $sessionkey): array
    {
        return [
            'status' => 1,
            'all_wind_skills'    => [
                'skill_1452','skill_1440','skill_1416','skill_1397','skill_1382','skill_1351','skill_1255','skill_1252','skill_1243','skill_1238',
                'skill_838','skill_837','skill_836','skill_835','skill_834','skill_833','skill_832','skill_831','skill_830','skill_829',
                'skill_828','skill_827','skill_826','skill_825','skill_824','skill_823','skill_822','skill_781','skill_780','skill_760',
                'skill_759','skill_757','skill_758','skill_937','skill_931','skill_742','skill_743','skill_744','skill_463','skill_490',
                'skill_661','skill_662','skill_663','skill_664','skill_665','skill_666','skill_667','skill_668','skill_669','skill_670',
                'skill_671','skill_672','skill_673','skill_674','skill_675','skill_676','skill_677','skill_678','skill_679','skill_680',
                'skill_166','skill_178','skill_215','skill_257','skill_252','skill_267','skill_262','skill_451','skill_456','skill_118',
                'skill_13','skill_16','skill_06','skill_17','skill_29','skill_30','skill_45','skill_46','skill_52','skill_39',
                'skill_69','skill_67','skill_74','skill_82','skill_85','skill_92','skill_97','skill_140','skill_102','skill_161',
                'skill_146','skill_123','skill_111','skill_151','skill_203','skill_171','skill_156','skill_285','skill_126',
                // String references in AMF are just repeats
                'skill_661','skill_662','skill_663','skill_664','skill_665','skill_666','skill_667','skill_668','skill_669','skill_670',
                'skill_671','skill_672','skill_673','skill_674','skill_675','skill_676','skill_677','skill_678','skill_679','skill_680',
                'skill_704','skill_721','skill_722','skill_723','skill_724','skill_725','skill_726','skill_727'
            ],
            'all_water_skills'   => [
                'skill_1456','skill_1439','skill_1418','skill_1401','skill_1379','skill_1355','skill_1256','skill_1248','skill_1244','skill_1239',
                'skill_873','skill_872','skill_871','skill_870','skill_869','skill_867','skill_866','skill_865','skill_864','skill_863',
                'skill_862','skill_861','skill_860','skill_859','skill_858','skill_857','skill_856','skill_787','skill_786','skill_773',
                'skill_772','skill_769','skill_770','skill_771','skill_941','skill_935','skill_745','skill_746','skill_747','skill_464',
                'skill_494','skill_641','skill_642','skill_643','skill_644','skill_645','skill_646','skill_647','skill_648','skill_649',
                'skill_650','skill_651','skill_652','skill_653','skill_654','skill_655','skill_656','skill_657','skill_658','skill_659',
                'skill_660','skill_170','skill_182','skill_261','skill_256','skill_219','skill_266','skill_272','skill_455','skill_460',
                'skill_121','skill_09','skill_24','skill_22','skill_33','skill_23','skill_58','skill_25','skill_40','skill_51',
                'skill_56','skill_60','skill_73','skill_64','skill_78','skill_84','skill_81','skill_89','skill_96','skill_110',
                'skill_101','skill_144','skill_106','skill_198','skill_165','skill_150','skill_196','skill_133','skill_116','skill_197',
                'skill_122','skill_160','skill_175','skill_207','skill_199','skill_268','skill_129','skill_200','skill_201','skill_202',
                'skill_208','skill_641','skill_642','skill_643','skill_644','skill_645','skill_646','skill_647','skill_648','skill_649',
                'skill_650','skill_651','skill_652','skill_653','skill_654','skill_655','skill_656','skill_657','skill_658','skill_659',
                'skill_660','skill_702','skill_728','skill_729','skill_730','skill_731','skill_732','skill_733','skill_734'
            ],
            'all_fire_skills'    => [
                'skill_1454','skill_1438','skill_1419','skill_1398','skill_1380','skill_1352','skill_1253','skill_1251','skill_1247','skill_1242',
                'skill_804','skill_803','skill_802','skill_801','skill_800','skill_799','skill_798','skill_797','skill_796','skill_795',
                'skill_794','skill_793','skill_792','skill_791','skill_790','skill_789','skill_788','skill_779','skill_778','skill_777',
                'skill_776','skill_774','skill_775','skill_938','skill_932','skill_751','skill_752','skill_753','skill_461','skill_491',
                'skill_601','skill_602','skill_603','skill_604','skill_605','skill_606','skill_607','skill_608','skill_609','skill_610',
                'skill_611','skill_612','skill_613','skill_614','skill_615','skill_616','skill_617','skill_618','skill_619','skill_620',
                'skill_167','skill_179','skill_216','skill_258','skill_253','skill_263','skill_269','skill_452','skill_457','skill_117',
                'skill_10','skill_20','skill_28','skill_11','skill_19','skill_18','skill_21','skill_47','skill_53','skill_36',
                'skill_70','skill_65','skill_75','skill_79','skill_86','skill_93','skill_98','skill_141','skill_103','skill_162',
                'skill_147','skill_130','skill_112','skill_152','skill_204','skill_157','skill_172','skill_234','skill_127','skill_601',
                'skill_602','skill_603','skill_604','skill_605','skill_606','skill_607','skill_608','skill_609','skill_610','skill_611',
                'skill_612','skill_613','skill_614','skill_615','skill_616','skill_617','skill_618','skill_619','skill_620','skill_701',
                'skill_706','skill_707','skill_708','skill_709','skill_710','skill_711','skill_712'
            ],
            'all_thunder_skills' => [
                'skill_1455','skill_1441','skill_1420','skill_1399','skill_1381','skill_1353','skill_1257','skill_1250','skill_1246','skill_1241',
                'skill_821','skill_820','skill_819','skill_818','skill_817','skill_816','skill_815','skill_814','skill_813','skill_812',
                'skill_811','skill_810','skill_809','skill_808','skill_807','skill_806','skill_805','skill_783','skill_782','skill_764',
                'skill_763','skill_761','skill_762','skill_939','skill_933','skill_754','skill_755','skill_756','skill_465','skill_492',
                'skill_681','skill_682','skill_683','skill_684','skill_685','skill_686','skill_687','skill_688','skill_689','skill_690',
                'skill_691','skill_692','skill_693','skill_694','skill_695','skill_696','skill_697','skill_698','skill_699','skill_168',
                'skill_180','skill_254','skill_217','skill_259','skill_270','skill_264','skill_453','skill_458','skill_119','skill_01',
                'skill_38','skill_14','skill_26','skill_05','skill_15','skill_27','skill_49','skill_54','skill_35','skill_71',
                'skill_66','skill_76','skill_83','skill_87','skill_94','skill_100','skill_142','skill_104','skill_163','skill_148',
                'skill_135','skill_113','skill_153','skill_158','skill_173','skill_205','skill_220','skill_124','skill_681','skill_682',
                'skill_683','skill_684','skill_685','skill_686','skill_687','skill_688','skill_689','skill_690','skill_691','skill_692',
                'skill_693','skill_694','skill_695','skill_696','skill_697','skill_698','skill_699','skill_705','skill_713','skill_714',
                'skill_715','skill_716','skill_717','skill_718','skill_719','skill_720'
            ],
            'all_earth_skills'   => [
                'skill_1453','skill_1442','skill_1417','skill_1400','skill_1383','skill_1354','skill_1254','skill_1249','skill_1245','skill_1240',
                'skill_855','skill_854','skill_853','skill_852','skill_851','skill_850','skill_849','skill_848','skill_847','skill_846',
                'skill_845','skill_844','skill_843','skill_842','skill_841','skill_840','skill_839','skill_785','skill_784','skill_768',
                'skill_767','skill_765','skill_766','skill_940','skill_934','skill_748','skill_749','skill_750','skill_462','skill_493',
                'skill_621','skill_622','skill_623','skill_624','skill_625','skill_626','skill_627','skill_628','skill_629','skill_630',
                'skill_631','skill_632','skill_633','skill_634','skill_635','skill_636','skill_637','skill_638','skill_639','skill_640',
                'skill_169','skill_181','skill_218','skill_260','skill_265','skill_255','skill_271','skill_454','skill_459','skill_132',
                'skill_12','skill_34','skill_07','skill_31','skill_32','skill_44','skill_48','skill_50','skill_55','skill_59',
                'skill_72','skill_63','skill_77','skill_80','skill_88','skill_95','skill_99','skill_143','skill_105','skill_164',
                'skill_149','skill_120','skill_114','skill_154','skill_159','skill_206','skill_174','skill_251','skill_128','skill_621',
                'skill_622','skill_623','skill_624','skill_625','skill_626','skill_627','skill_628','skill_629','skill_630','skill_631',
                'skill_632','skill_633','skill_634','skill_635','skill_636','skill_637','skill_638','skill_639','skill_640','skill_703',
                'skill_735','skill_736','skill_737','skill_738','skill_739','skill_740','skill_741'
            ],
            'all_taijutsu_skills' => [
                'skill_1451','skill_1450','skill_1445','skill_1444','skill_1443','skill_1350','skill_1838','skill_1837','skill_1836','skill_1835',
                'skill_1821','skill_1820','skill_1819','skill_1818','skill_1817','skill_1816','skill_1415','skill_1424','skill_1423','skill_1815',
                'skill_1814','skill_1813','skill_1812','skill_1811','skill_1810','skill_1396','skill_1395','skill_1394','skill_1393','skill_1392',
                'skill_1391','skill_1376','skill_1375','skill_1378','skill_1744','skill_1743','skill_997','skill_1356','skill_1367','skill_1366',
                'skill_1365','skill_1364','skill_1357','skill_1364','skill_1365','skill_1366','skill_1367','skill_1742','skill_1741','skill_1740',
                'skill_1739','skill_1738','skill_1737','skill_1736','skill_1735','skill_1734','skill_1733','skill_1732','skill_1731','skill_1730',
                'skill_1729','skill_1728','skill_1727','skill_1726','skill_1725','skill_1333','skill_1332','skill_1331','skill_1327','skill_1322',
                'skill_1321','skill_469','skill_470','skill_468','skill_467','skill_466','skill_1320','skill_1319','skill_1316','skill_1310',
                'skill_1309','skill_1302','skill_1301','skill_1297','skill_1288','skill_1285','skill_1280','skill_1279','skill_1264','skill_1263',
                'skill_1259','skill_1258','skill_1226','skill_1225','skill_1222','skill_1224','skill_1227','skill_971','skill_967','skill_968',
                'skill_969','skill_970','skill_956','skill_943','skill_942','skill_936','skill_926','skill_920','skill_902','skill_443',
                'skill_434','skill_431','skill_432','skill_430','skill_427','skill_426','skill_425','skill_421','skill_414','skill_415',
                'skill_868','skill_409','skill_410','skill_411','skill_413','skill_404','skill_397','skill_382','skill_187','skill_195',
                'skill_366','skill_370','skill_364','skill_373','skill_375','skill_376','skill_360','skill_356','skill_357','skill_188',
                'skill_194','skill_350','skill_346','skill_332','skill_334','skill_335','skill_336','skill_337','skill_338','skill_339',
                'skill_341','skill_342','skill_320','skill_321','skill_324','skill_325','skill_327','skill_301','skill_303','skill_316',
                'skill_317','skill_41','skill_42','skill_43','skill_68','skill_62','skill_61','skill_107','skill_90','skill_139',
                'skill_108','skill_189','skill_191','skill_186','skill_190','skill_139','skill_108','skill_400','skill_192','skill_193',
                'skill_385','skill_386','skill_387','skill_388','skill_389','skill_1200','skill_1202'
            ],
            'all_genjutsu_skills' => [
                'skill_1458','skill_1457','skill_1449','skill_1448','skill_1447','skill_1446','skill_1839','skill_1840','skill_1841','skill_1745',
                'skill_1746','skill_1747','skill_1748','skill_1749','skill_1834','skill_1833','skill_1832','skill_1831','skill_1830','skill_1829',
                'skill_1828','skill_1827','skill_1826','skill_1825','skill_1432','skill_1823','skill_1822','skill_1414','skill_1413','skill_1412',
                'skill_1411','skill_1410','skill_1427','skill_1426','skill_1425','skill_1809','skill_1808','skill_1387','skill_1386','skill_1385',
                'skill_1384','skill_1377','skill_1807','skill_1806','skill_1805','skill_1804','skill_1803','skill_1363','skill_1362','skill_1361',
                'skill_1360','skill_1359','skill_1358','skill_1802','skill_1801','skill_1344','skill_1343','skill_1336','skill_1335','skill_1334',
                'skill_1330','skill_1329','skill_1328','skill_1724','skill_1723','skill_1722','skill_1721','skill_1720','skill_1719','skill_1718',
                'skill_1717','skill_1716','skill_1715','skill_1714','skill_1713','skill_1712','skill_1711','skill_1710','skill_1709','skill_1708',
                'skill_1707','skill_1706','skill_1705','skill_1704','skill_1703','skill_1702','skill_1701','skill_1315','skill_1314','skill_1303',
                'skill_1298','skill_1296','skill_1295','skill_1294','skill_1289','skill_1287','skill_1286','skill_1284','skill_1283','skill_1278',
                'skill_1277','skill_1276','skill_1275','skill_1274','skill_1273','skill_1270','skill_1269','skill_1237','skill_1223','skill_1234',
                'skill_1233','skill_1232','skill_1231','skill_1230','skill_1236','skill_1235','skill_966','skill_899','skill_974','skill_962',
                'skill_957','skill_958','skill_959','skill_960','skill_961','skill_949','skill_948','skill_947','skill_946','skill_945',
                'skill_944','skill_928','skill_927','skill_919','skill_918','skill_917','skill_912','skill_910','skill_903','skill_450',
                'skill_448','skill_446','skill_444','skill_439','skill_440','skill_441','skill_442','skill_435','skill_436','skill_437',
                'skill_429','skill_428','skill_422','skill_423','skill_420','skill_419','skill_418','skill_417','skill_408','skill_412',
                'skill_407','skill_396','skill_401','skill_402','skill_403','skill_405','skill_383','skill_381','skill_380','skill_379',
                'skill_378','skill_377','skill_371','skill_363','skill_367','skill_368','skill_369','skill_372','skill_374','skill_361',
                'skill_362','skill_358','skill_359','skill_355','skill_354','skill_351','skill_352','skill_353','skill_349','skill_348',
                'skill_347','skill_345','skill_398','skill_343','skill_344','skill_333','skill_340','skill_330','skill_331','skill_329',
                'skill_328','skill_319','skill_322','skill_323','skill_326','skill_184','skill_399','skill_310','skill_318','skill_309',
                'skill_308','skill_307','skill_306','skill_305','skill_304','skill_314','skill_313','skill_312','skill_311','skill_302',
                'skill_03','skill_04','skill_57','skill_365','skill_390','skill_391','skill_392','skill_393','skill_394','skill_395',
                'skill_1201','skill_1203','skill_1204','skill_1205','skill_1210','skill_1211'
            ],
            'all_clan_skills'     => [
                'skill_2111','skill_2110','skill_2109','skill_2108','skill_2107','skill_2106','skill_2105','skill_2104','skill_2103','skill_2102',
                'skill_2101','skill_2100','skill_598','skill_597','skill_596','skill_595','skill_594','skill_593','skill_592','skill_591',
                'skill_590','skill_589','skill_588','skill_587','skill_586','skill_585','skill_584','skill_583','skill_582','skill_581',
                'skill_580','skill_579','skill_573','skill_574','skill_575','skill_576','skill_577','skill_578','skill_499','skill_500',
                'skill_501','skill_502','skill_503','skill_504','skill_505','skill_506','skill_507','skill_508','skill_509','skill_510',
                'skill_511','skill_512','skill_513','skill_514','skill_515','skill_516','skill_517','skill_518','skill_519','skill_520',
                'skill_521','skill_522','skill_523','skill_524','skill_525','skill_526','skill_527','skill_528','skill_529','skill_530',
                'skill_531','skill_532','skill_533','skill_534','skill_535','skill_536','skill_537','skill_538','skill_539','skill_540',
                'skill_541','skill_542','skill_543','skill_544','skill_545','skill_546','skill_547','skill_548','skill_549','skill_550',
                'skill_551','skill_552','skill_553','skill_554','skill_555','skill_556','skill_557','skill_558','skill_559','skill_560',
                'skill_561','skill_562','skill_563','skill_564','skill_565','skill_566','skill_567','skill_568','skill_569','skill_570',
                'skill_571','skill_572'
            ],
            'all_arena_skills'    => [
                'skill_1431','skill_1430','skill_1348','skill_1347','skill_1346','skill_1345','skill_1340','skill_1339','skill_1326','skill_1325',
                'skill_1308','skill_1307','skill_1313','skill_1312','skill_1293','skill_1292','skill_1291','skill_1290','skill_1272','skill_1271',
                'skill_1260','skill_1221','skill_1220','skill_1217','skill_1216','skill_1206','skill_1207','skill_965','skill_964','skill_954',
                'skill_955','skill_929','skill_930','skill_921','skill_922','skill_915','skill_916','skill_913','skill_914','skill_447',
                'skill_449','skill_900','skill_901','skill_908','skill_909','skill_977'
            ],
            'all_crew_skills'     => [
                'skill_1407','skill_1406','skill_1390','skill_1349','skill_1371','skill_996','skill_995','skill_994','skill_993','skill_1311',
                'skill_1304','skill_992','skill_991','skill_990','skill_989','skill_988','skill_987','skill_986','skill_985','skill_984',
                'skill_983','skill_982','skill_981','skill_979','skill_978','skill_976','skill_975','skill_980'
            ],
            'all_ranking_skills'  => [
                'skill_1405','skill_1404','skill_1429','skill_1428','skill_1389','skill_1388','skill_1372','skill_1373','skill_1338','skill_1337',
                'skill_1318','skill_1317','skill_1300','skill_1299','skill_1282','skill_1281','skill_1262','skill_1261','skill_1266','skill_1265',
                'skill_1215','skill_1214','skill_1209','skill_1208','skill_972','skill_973','skill_952','skill_951','skill_923','skill_950',
                'skill_924','skill_925','skill_495','skill_496','skill_433'
            ],
            'all_pass_skills'     => [
                'skill_1422','skill_1421','skill_1368','skill_1374','skill_1342','skill_1341','skill_1324','skill_1323','skill_1306','skill_1305',
                'skill_1267','skill_1268','skill_1228','skill_1229','skill_1212','skill_1213','skill_384','skill_406','skill_416','skill_424',
                'skill_438','skill_445','skill_498','skill_911','skill_497','skill_953','skill_963'
            ],
            'all_coop_skills'     => [
                'skill_1434','skill_1433','skill_1409','skill_1408','skill_1402','skill_1403','skill_1370','skill_1369'
            ],
        ];
    }

    public function getItemFavoriteData($char_id, $sessionkey): array
    {
        $char = Character::find((int) $char_id);
        if (!$char) {
            return [];
        }

        $favorites = $char->char_favorites ? explode(',', $char->char_favorites) : [];
        return $favorites;
    }

    public function toogleItemFavorite($char_id, $sessionkey, $item_id): array
    {
        $char = Character::find((int) $char_id);
        if (!$char) {
            return [];
        }

        $favorites = $char->char_favorites ? explode(',', $char->char_favorites) : [];
        
        if (in_array((string)$item_id, $favorites)) {
            $favorites = array_values(array_filter($favorites, fn($f) => $f !== (string)$item_id));
        } else {
            $favorites[] = (string)$item_id;
        }

        $char->char_favorites = implode(',', $favorites);
        $char->save();

        return $favorites;
    }
}

// Helper to sanitize incoming AMF loosely typed data if needed inside Arrays
function cloneVal($val) {
    return is_array($val) ? cloneVal($val[0]) : $val;
}
