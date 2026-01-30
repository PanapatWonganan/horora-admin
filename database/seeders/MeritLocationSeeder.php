<?php

namespace Database\Seeders;

use App\Models\MeritLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MeritLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'id' => Str::uuid(),
                'name_th' => 'พระพิฆเนศ ห้วยขวาง',
                'name_en' => 'Ganesha Shrine Huai Khwang',
                'description' => 'ศาลพระพิฆเนศที่ศักดิ์สิทธิ์ ใกล้สถานีรถไฟฟ้า MRT ห้วยขวาง',
                'belief' => 'ขอโชคลาภ การงาน การเรียน ขจัดอุปสรรค',
                'address' => 'ถนนประชาสงเคราะห์ แขวงห้วยขวาง เขตห้วยขวาง กรุงเทพฯ',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'id' => Str::uuid(),
                'name_th' => 'วัดเล่งเน่ยยี่ (วัดมังกรกมลาวาส)',
                'name_en' => 'Wat Mangkon Kamalawat',
                'description' => 'วัดจีนที่เก่าแก่และใหญ่ที่สุดในประเทศไทย ตั้งอยู่ในย่านเยาวราช',
                'belief' => 'เจ้าแม่กวนอิม - สุขภาพ ลูกหลาน ครอบครัว ขอบุตร',
                'address' => 'ถนนเจริญกรุง แขวงป้อมปราบ เขตป้อมปราบศัตรูพ่าย กรุงเทพฯ',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'id' => Str::uuid(),
                'name_th' => 'ศาลหลักเมือง',
                'name_en' => 'City Pillar Shrine',
                'description' => 'ศาลหลักเมืองกรุงเทพมหานคร สถานที่ศักดิ์สิทธิ์คู่บ้านคู่เมือง',
                'belief' => 'ขอพร ความเป็นสิริมงคล ปกป้องคุ้มครอง',
                'address' => 'ถนนหลักเมือง แขวงพระบรมมหาราชวัง เขตพระนคร กรุงเทพฯ',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'id' => Str::uuid(),
                'name_th' => 'วัดพระแก้ว',
                'name_en' => 'Temple of the Emerald Buddha',
                'description' => 'วัดพระศรีรัตนศาสดาราม วัดประจำพระราชวังในพระบรมมหาราชวัง',
                'belief' => 'บูชาพระแก้วมรกต ความเป็นสิริมงคลสูงสุด',
                'address' => 'ถนนหน้าพระลาน แขวงพระบรมมหาราชวัง เขตพระนคร กรุงเทพฯ',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'id' => Str::uuid(),
                'name_th' => 'วัดสุทัศนเทพวราราม',
                'name_en' => 'Wat Suthat Thepwararam',
                'description' => 'วัดหลวงชั้นเอกพิเศษ ภายในมีพระศรีศากยมุนี พระพุทธรูปขนาดใหญ่',
                'belief' => 'ขอพรสมหวัง ความสงบสุข',
                'address' => 'ถนนบำรุงเมือง แขวงวัดราชบพิธ เขตพระนคร กรุงเทพฯ',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($locations as $location) {
            MeritLocation::firstOrCreate(
                ['name_th' => $location['name_th']],
                $location
            );
        }
    }
}
