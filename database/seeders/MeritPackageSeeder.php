<?php

namespace Database\Seeders;

use App\Models\MeritPackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MeritPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'id' => Str::uuid(),
                'name_th' => 'แพ็คเกจพื้นฐาน',
                'name_en' => 'Basic Package',
                'description' => 'ไหว้พระและถ่ายรูปหลักฐานส่งให้ 3 รูป',
                'items' => json_encode(['ธูป 3 ดอก', 'เทียน 1 คู่', 'ดอกไม้ 1 กระถาง']),
                'price' => 199,
                'photo_count' => 3,
                'has_video' => false,
                'has_live' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'id' => Str::uuid(),
                'name_th' => 'แพ็คเกจมาตรฐาน',
                'name_en' => 'Standard Package',
                'description' => 'ไหว้พระพร้อมวิดีโอบันทึกขณะทำพิธี',
                'items' => json_encode(['ธูป 9 ดอก', 'เทียน 1 คู่', 'ดอกบัว 1 ดอก', 'ผลไม้ 1 ชุด']),
                'price' => 399,
                'photo_count' => 5,
                'has_video' => true,
                'has_live' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'id' => Str::uuid(),
                'name_th' => 'แพ็คเกจพรีเมียม',
                'name_en' => 'Premium Package',
                'description' => 'ไหว้พระพร้อม Live สดให้ดูขณะทำพิธี',
                'items' => json_encode(['ธูป 12 ดอก', 'เทียน 2 คู่', 'ดอกบัว 3 ดอก', 'ผลไม้ 5 อย่าง', 'ขนมไทย 1 ชุด']),
                'price' => 799,
                'photo_count' => 10,
                'has_video' => true,
                'has_live' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'id' => Str::uuid(),
                'name_th' => 'แพ็คเกจพิเศษ VIP',
                'name_en' => 'VIP Package',
                'description' => 'พิธีพิเศษโดยพราหมณ์หรือพระสงฆ์ พร้อม Live สดตลอดพิธี',
                'items' => json_encode(['ชุดบูชาครบ', 'พานพุ่ม', 'ดอกไม้สด', 'ผลไม้ 9 อย่าง', 'ขนมไทย', 'น้ำมนต์ส่งให้']),
                'price' => 1599,
                'photo_count' => 15,
                'has_video' => true,
                'has_live' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($packages as $package) {
            MeritPackage::firstOrCreate(
                ['name_th' => $package['name_th']],
                $package
            );
        }
    }
}
