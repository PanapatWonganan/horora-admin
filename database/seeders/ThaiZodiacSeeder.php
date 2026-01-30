<?php

namespace Database\Seeders;

use App\Models\ThaiZodiacAnimal;
use App\Models\ThaiElement;
use Illuminate\Database\Seeder;

class ThaiZodiacSeeder extends Seeder
{
    public function run(): void
    {
        $animals = [
            ['animal_name' => 'ชวด', 'thai_name' => 'ปีชวด', 'english_name' => 'Rat', 'characteristics' => 'ฉลาด ปราดเปรื่อง รอบคอบ มีไหวพริบ'],
            ['animal_name' => 'ฉลู', 'thai_name' => 'ปีฉลู', 'english_name' => 'Ox', 'characteristics' => 'ขยัน อดทน ซื่อสัตย์ มั่นคง'],
            ['animal_name' => 'ขาล', 'thai_name' => 'ปีขาล', 'english_name' => 'Tiger', 'characteristics' => 'กล้าหาญ มีพลัง เป็นผู้นำ มีเสน่ห์'],
            ['animal_name' => 'เถาะ', 'thai_name' => 'ปีเถาะ', 'english_name' => 'Rabbit', 'characteristics' => 'อ่อนโยน สุภาพ มีศิลปะ โชคดี'],
            ['animal_name' => 'มะโรง', 'thai_name' => 'ปีมะโรง', 'english_name' => 'Dragon', 'characteristics' => 'ทะเยอทะยาน มีอำนาจ โชคลาภ ความสำเร็จ'],
            ['animal_name' => 'มะเส็ง', 'thai_name' => 'ปีมะเส็ง', 'english_name' => 'Snake', 'characteristics' => 'ปราดเปรื่อง ลึกลับ มีเสน่ห์ หยั่งรู้'],
            ['animal_name' => 'มะเมีย', 'thai_name' => 'ปีมะเมีย', 'english_name' => 'Horse', 'characteristics' => 'รักอิสระ กระตือรือร้น มีพลัง สนุกสนาน'],
            ['animal_name' => 'มะแม', 'thai_name' => 'ปีมะแม', 'english_name' => 'Goat', 'characteristics' => 'อ่อนโยน สร้างสรรค์ มีศิลปะ สงบ'],
            ['animal_name' => 'วอก', 'thai_name' => 'ปีวอก', 'english_name' => 'Monkey', 'characteristics' => 'ฉลาด คล่องแคล่ว สนุกสนาน มีไหวพริบ'],
            ['animal_name' => 'ระกา', 'thai_name' => 'ปีระกา', 'english_name' => 'Rooster', 'characteristics' => 'ขยัน มีระเบียบ กล้าหาญ ตรงไปตรงมา'],
            ['animal_name' => 'จอ', 'thai_name' => 'ปีจอ', 'english_name' => 'Dog', 'characteristics' => 'ซื่อสัตย์ จงรักภักดี ยุติธรรม เชื่อถือได้'],
            ['animal_name' => 'กุน', 'thai_name' => 'ปีกุน', 'english_name' => 'Pig', 'characteristics' => 'ใจดี มีน้ำใจ ซื่อตรง โชคลาภ'],
        ];

        foreach ($animals as $animal) {
            ThaiZodiacAnimal::firstOrCreate(
                ['animal_name' => $animal['animal_name']],
                $animal
            );
        }

        $elements = [
            ['element_name' => 'ทอง', 'element_full' => 'ธาตุทอง (กิม)', 'lucky_colors' => json_encode(['ทอง', 'ขาว', 'เงิน']), 'characteristics' => 'มีความมั่นคง เด็ดเดี่ยว มุ่งมั่น แข็งแกร่ง'],
            ['element_name' => 'น้ำ', 'element_full' => 'ธาตุน้ำ (จุ้ย)', 'lucky_colors' => json_encode(['ดำ', 'น้ำเงิน', 'เทา']), 'characteristics' => 'ปรับตัวเก่ง มีปัญญา ลึกซึ้ง อ่อนโยน'],
            ['element_name' => 'ไม้', 'element_full' => 'ธาตุไม้ (บ๊ก)', 'lucky_colors' => json_encode(['เขียว', 'น้ำตาล', 'ครีม']), 'characteristics' => 'เติบโต สร้างสรรค์ มีน้ำใจ อดทน'],
            ['element_name' => 'ไฟ', 'element_full' => 'ธาตุไฟ (ฮ๊วย)', 'lucky_colors' => json_encode(['แดง', 'ส้ม', 'ชมพู']), 'characteristics' => 'กระตือรือร้น มีพลัง เป็นผู้นำ กล้าหาญ'],
            ['element_name' => 'ดิน', 'element_full' => 'ธาตุดิน (โท้)', 'lucky_colors' => json_encode(['เหลือง', 'น้ำตาล', 'ส้ม']), 'characteristics' => 'มั่นคง อดทน เชื่อถือได้ ปฏิบัติจริง'],
        ];

        foreach ($elements as $element) {
            ThaiElement::firstOrCreate(
                ['element_name' => $element['element_name']],
                $element
            );
        }
    }
}
