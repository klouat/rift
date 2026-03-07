<?php

namespace App\Services;

class PlayStoreService
{
    // All packages exactly as the real server returns them.
    // String Reference entries in the AMF trace are just the same string reused —
    // PHP will serialize them normally and SabreAMF will handle deduplication.
    private const PACKAGES = [
        'air.ninjarift.package_1'     => ['tokens_30000','material_69_100','pet_akuma_gobi'],
        'air.ninjarift.package_2'     => ['tokens_13500','material_69_40','pet_akuma_gobi'],
        'air.ninjarift.package_3'     => ['tokens_5000','material_69_20','pet_akuma_gobi'],
        'air.ninjarift.package_4'     => ['tokens_2200','pet_akuma_gobi'],
        'air.ninjarift.package_5'     => ['tokens_300'],
        'air.ninjarift.package_6_1_0' => ['battle_pass_season_1_0'],
        'air.ninjarift.package_6_1_1' => ['battle_pass_season_1_1'],
        'air.ninjarift.package_6_2_0' => ['battle_pass_season_2_0'],
        'air.ninjarift.package_6_2_1' => [],
        'air.ninjarift.package_6_3_0' => ['battle_pass_season_3_0'],
        'air.ninjarift.package_6_3_1' => ['battle_pass_season_3_1'],
        'air.ninjarift.package_6_4_0' => ['battle_pass_season_4_0'],
        'air.ninjarift.package_6_4_1' => ['battle_pass_season_4_1'],
        'air.ninjarift.package_6_5_0' => ['battle_pass_season_5_0'],
        'air.ninjarift.package_6_5_1' => ['battle_pass_season_5_1'],
        'air.ninjarift.package_6_6_0' => ['battle_pass_season_6_0'],
        'air.ninjarift.package_6_6_1' => ['battle_pass_season_6_1'],
        'air.ninjarift.package_6_7_0' => ['battle_pass_season_7_0'],
        'air.ninjarift.package_6_7_1' => ['battle_pass_season_7_1'],
        'air.ninjarift.package_6_8_0' => ['battle_pass_season_8_0'],
        'air.ninjarift.package_6_8_1' => ['battle_pass_season_8_1'],
        'air.ninjarift.package_6_9_0' => ['battle_pass_season_9_0'],
        'air.ninjarift.package_6_9_1' => [],
        'air.ninjarift.package_6_10_0'=> ['battle_pass_season_10_0'],
        'air.ninjarift.package_6_10_1'=> ['battle_pass_season_10_1'],
        'air.ninjarift.package_6_11_0'=> ['battle_pass_season_11_0'],
        'air.ninjarift.package_6_11_1'=> ['battle_pass_season_11_1'],
        'air.ninjarift.package_6_12_0'=> ['battle_pass_season_12_0'],
        'air.ninjarift.package_6_12_1'=> ['battle_pass_season_12_1'],
        'air.ninjarift.package_6_13_0'=> ['battle_pass_season_13_0'],
        'air.ninjarift.package_6_13_1'=> ['battle_pass_season_13_1'],
        'air.ninjarift.package_6_14_0'=> ['battle_pass_season_14_0'],
        'air.ninjarift.package_6_14_1'=> ['battle_pass_season_14_1'],
        'air.ninjarift.package_6_15_0'=> ['battle_pass_season_15_0'],
        'air.ninjarift.package_6_15_1'=> ['battle_pass_season_15_1'],
        'air.ninjarift.package_6_16_0'=> ['battle_pass_season_16_0'],
        'air.ninjarift.package_6_16_1'=> ['battle_pass_season_16_1'],
        'air.ninjarift.package_6_17_0'=> ['battle_pass_season_17_0'],
        'air.ninjarift.package_6_17_1'=> ['battle_pass_season_17_1'],
        'air.ninjarift.package_6_18_0'=> ['battle_pass_season_18_0'],
        'air.ninjarift.package_6_18_1'=> ['battle_pass_season_18_1'],
        'air.ninjarift.package_6_19_0'=> ['battle_pass_season_19_0'],
        'air.ninjarift.package_6_19_1'=> ['battle_pass_season_19_1'],
        'air.ninjarift.package_7'     => ['emblem_basic','tokens_2200','pet_akuma_gobi'],
        'air.ninjarift.package_8'     => ['emblem_plus','tokens_2200','pet_akuma_gobi'],
        'air.ninjarift.package_9'     => ['upgrade_emblem','pet_akuma_gobi'],
        'air.ninjarift.package_10_1'  => ['material_69_3000','material_66_275','essential_85_1','pet_akuma_gobi'],
        'air.ninjarift.package_10_3'  => ['material_69_500','material_66_50','pet_akuma_gobi'],
        'air.ninjarift.package_11_0'  => ['emblem_basic','tokens_13500','wpn_301','back_41','set_101_0','hair_31_0','skill_302'],
        'air.ninjarift.package_11_1'  => ['emblem_basic','tokens_30000','wpn_302','back_42','set_102_0','hair_32_0','skill_301','pet_jyubi'],
        'air.ninjarift.package_12_1'  => [''],
        'air.ninjarift.package_12_2'  => [''],
        'air.ninjarift.package_12_3'  => [''],
        'air.ninjarift.package_12_4'  => [''],
        'air.ninjarift.package_12_5'  => [''],
        'air.ninjarift.package_12_6'  => [''],
        'air.ninjarift.package_12_7'  => [''],
        'air.ninjarift.package_12_8'  => [''],
        'air.ninjarift.package_12_9'  => [''],
        'air.ninjarift.package_12_10' => [''],
        'air.ninjarift.package_13'    => ['essential_05_1400','essential_71_150','essential_80_1','pet_akuma_gobi'],
        'air.ninjarift.package_14'    => ['essential_05_200','essential_71_50','pet_akuma_gobi'],
        'air.ninjarift.package_15'    => ['essential_103_500','essential_74_575','essential_25_30','pet_akuma_gobi'],
        'air.ninjarift.package_16'    => ['essential_103_100','essential_63_200','essential_83_1','pet_akuma_gobi'],
        'air.ninjarift.package_17'    => ['material_296_70','material_297_70','material_298_70','material_299_30'],
        'air.ninjarift.package_18'    => ['material_340_60','material_341_60','material_342_60','material_343_60','material_344_60','material_345_60','material_346_60'],
    ];

    public function executeService($action, $params = [])
    {
        switch ($action) {
            case 'fetchProducts':
                return self::PACKAGES;
            default:
                return ['status' => 1];
        }
    }
}
