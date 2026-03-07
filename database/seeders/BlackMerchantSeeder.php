<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MerchantPackage;

class BlackMerchantSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['package_id' => 'package_1', 'skills' => ['skill_316', 'skill_317'], 'prices' => [3999, 1999]],
            ['package_id' => 'package_2', 'skills' => ['skill_330', 'skill_331'], 'advanced_skills' => ['skill_1829', 'skill_1830', 'skill_1831'], 'prices' => [2999, 1999]],
            ['package_id' => 'package_3', 'skills' => ['skill_343', 'skill_344'], 'advanced_skills' => ['skill_1801', 'skill_1802'], 'prices' => [2999, 1999]],
            ['package_id' => 'package_4', 'skills' => ['skill_354', 'skill_355'], 'prices' => [2999, 1999]],
            ['package_id' => 'package_5', 'skills' => ['skill_407'], 'advanced_skills' => ['skill_1805', 'skill_1806', 'skill_1807'], 'prices' => [5999]],
            ['package_id' => 'package_6', 'skills' => ['skill_414', 'skill_415'], 'prices' => [4999, 2999]],
            ['package_id' => 'package_7', 'skills' => ['skill_395'], 'prices' => [9999]],
            ['package_id' => 'package_8', 'skills' => ['skill_383'], 'prices' => [5999]],
            ['package_id' => 'package_9', 'skills' => ['skill_379', 'skill_377', 'skill_378'], 'prices' => [3999, 999, 499]],
            ['package_id' => 'package_10', 'skills' => ['skill_361', 'skill_362'], 'prices' => [2599, 1399]],
            ['package_id' => 'package_11', 'skills' => ['skill_422', 'skill_423'], 'prices' => [5999, 4499]],
            ['package_id' => 'package_12', 'skills' => ['skill_429', 'skill_428'], 'prices' => [499, 3999]],
            ['package_id' => 'package_13', 'skills' => ['skill_431', 'skill_432'], 'advanced_skills' => ['skill_1819', 'skill_1820', 'skill_1821'], 'prices' => [3999, 1999]],
            ['package_id' => 'package_14', 'skills' => ['skill_437'], 'prices' => [7999]],
            ['package_id' => 'package_15', 'skills' => ['skill_443'], 'prices' => [5999]],
            ['package_id' => 'package_16', 'skills' => ['skill_912'], 'advanced_skills' => ['skill_1839', 'skill_1840', 'skill_1841'], 'prices' => [7999]],
            ['package_id' => 'package_17', 'skills' => ['skill_945', 'skill_946'], 'advanced_skills' => ['skill_1824', 'skill_1825', 'skill_1826'], 'prices' => [4999, 999]],
            ['package_id' => 'package_18', 'skills' => ['skill_957', 'skill_958'], 'prices' => [4999, 999]],
            ['package_id' => 'package_19', 'skills' => ['skill_1204', 'skill_1205'], 'advanced_skills' => ['skill_1803', 'skill_1804'], 'prices' => [3749, 3375]],
            ['package_id' => 'package_20', 'skills' => ['skill_967', 'skill_968', 'skill_969', 'skill_970'], 'prices' => [3999, 999, 499, 249]],
            ['package_id' => 'package_21', 'skills' => ['skill_966'], 'prices' => [7199]],
            ['package_id' => 'package_22', 'skills' => ['skill_961'], 'advanced_skills' => ['skill_1827', 'skill_1828'], 'prices' => [7199]],
            ['package_id' => 'package_23', 'skills' => ['skill_928'], 'advanced_skills' => ['skill_1808', 'skill_1809'], 'prices' => [4999]],
            ['package_id' => 'package_24', 'skills' => ['skill_917'], 'advanced_skills' => ['skill_1822', 'skill_1823'], 'prices' => [4999]],
            ['package_id' => 'package_25', 'skills' => ['skill_444'], 'prices' => [4999]],
            ['package_id' => 'package_26', 'skills' => ['skill_448', 'skill_910'], 'prices' => [4999, 999]],
            ['package_id' => 'package_27', 'skills' => ['skill_1235', 'skill_1236'], 'prices' => [4999, 999]],
            ['package_id' => 'package_28', 'skills' => ['skill_1258', 'skill_1259'], 'prices' => [4999, 999]],
            ['package_id' => 'package_29', 'skills' => ['skill_1273', 'skill_1274'], 'prices' => [4999, 999]],
            ['package_id' => 'package_30', 'skills' => ['skill_1278'], 'prices' => [999]],
            ['package_id' => 'package_31', 'skills' => ['skill_1279', 'skill_1280'], 'prices' => [4999, 999]],
            ['package_id' => 'package_32', 'skills' => ['skill_1288'], 'prices' => [4999]],
            ['package_id' => 'package_33', 'skills' => ['skill_1289'], 'prices' => [4999]],
            ['package_id' => 'package_34', 'skills' => ['skill_1294', 'skill_1295'], 'advanced_skills' => ['skill_1832', 'skill_1833', 'skill_1834'], 'prices' => [4999, 999]],
            ['package_id' => 'package_35', 'skills' => ['skill_1302'], 'advanced_skills' => ['skill_1810', 'skill_1811', 'skill_1812'], 'prices' => [4999]],
            ['package_id' => 'package_36', 'skills' => ['skill_1301'], 'prices' => [4999]],
            ['package_id' => 'package_37', 'skills' => ['skill_351'], 'prices' => [2499]],
            ['package_id' => 'package_38', 'skills' => ['skill_425'], 'prices' => [2499]],
            ['package_id' => 'package_39', 'skills' => ['skill_1320'], 'advanced_skills' => ['skill_1837', 'skill_1838'], 'prices' => [2499]],
            ['package_id' => 'package_40', 'skills' => ['skill_1309', 'skill_1310'], 'prices' => [2999, 1199]],
            ['package_id' => 'package_41', 'skills' => ['skill_1321', 'skill_1322'], 'advanced_skills' => ['skill_1322', 'skill_1813', 'skill_1814', 'skill_1815'], 'prices' => [2999, 1199]],
            ['package_id' => 'package_42', 'skills' => ['skill_1327'], 'advanced_skills' => ['skill_1835', 'skill_1836'], 'prices' => [2499]],
            ['package_id' => 'package_43', 'skills' => ['skill_1328', 'skill_1329'], 'prices' => [2999, 1199]],
            ['package_id' => 'package_44', 'skills' => ['skill_1343', 'skill_1344'], 'prices' => [2999, 1199]],
            ['package_id' => 'package_45', 'skills' => ['skill_1364', 'skill_1365', 'skill_1366', 'skill_1367'], 'prices' => [1740, 3140, 4540, 5940]],
            ['package_id' => 'package_46', 'skills' => ['skill_1375', 'skill_1376'], 'prices' => [3499, 3499]],
            ['package_id' => 'package_47', 'skills' => ['skill_1377'], 'prices' => [4999]],
            ['package_id' => 'package_48', 'skills' => ['skill_1391', 'skill_1392'], 'prices' => []],
            ['package_id' => 'package_49', 'skills' => ['skill_1395'], 'prices' => [4999]],
            ['package_id' => 'package_50', 'skills' => ['skill_1410', 'skill_1411', 'skill_1412', 'skill_1413'], 'prices' => [599, 1199, 1799, 2399]],
            ['package_id' => 'package_51', 'skills' => ['skill_1415'], 'prices' => [4799]],
        ];

        foreach ($packages as $package) {
            MerchantPackage::updateOrCreate(
                ['package_id' => $package['package_id']],
                $package
            );
        }
    }
}
