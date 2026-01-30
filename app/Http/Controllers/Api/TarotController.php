<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TarotReading;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TarotController extends Controller
{
    private $tarotCards = [
        ['name' => 'The Fool', 'name_th' => 'คนโง่', 'meaning' => 'การเริ่มต้นใหม่ ความบริสุทธิ์'],
        ['name' => 'The Magician', 'name_th' => 'นักมายากล', 'meaning' => 'พลังสร้างสรรค์ ความสามารถ'],
        ['name' => 'The High Priestess', 'name_th' => 'นักบวชหญิง', 'meaning' => 'สัญชาตญาณ ความลึกลับ'],
        ['name' => 'The Empress', 'name_th' => 'จักรพรรดินี', 'meaning' => 'ความอุดมสมบูรณ์ ความรัก'],
        ['name' => 'The Emperor', 'name_th' => 'จักรพรรดิ', 'meaning' => 'อำนาจ ความมั่นคง'],
        ['name' => 'The Hierophant', 'name_th' => 'พระสันตะปาปา', 'meaning' => 'ประเพณี คำสอน'],
        ['name' => 'The Lovers', 'name_th' => 'คู่รัก', 'meaning' => 'ความรัก ทางเลือก'],
        ['name' => 'The Chariot', 'name_th' => 'รถศึก', 'meaning' => 'ชัยชนะ ความมุ่งมั่น'],
        ['name' => 'Strength', 'name_th' => 'พลัง', 'meaning' => 'ความกล้าหาญ ความอดทน'],
        ['name' => 'The Hermit', 'name_th' => 'ฤๅษี', 'meaning' => 'การค้นหาตัวเอง ปัญญา'],
        ['name' => 'Wheel of Fortune', 'name_th' => 'วงล้อแห่งโชค', 'meaning' => 'โชคชะตา การเปลี่ยนแปลง'],
        ['name' => 'Justice', 'name_th' => 'ความยุติธรรม', 'meaning' => 'ความเป็นธรรม สมดุล'],
        ['name' => 'The Hanged Man', 'name_th' => 'ชายแขวน', 'meaning' => 'การเสียสละ มุมมองใหม่'],
        ['name' => 'Death', 'name_th' => 'ความตาย', 'meaning' => 'การเปลี่ยนแปลง การสิ้นสุด'],
        ['name' => 'Temperance', 'name_th' => 'ความพอดี', 'meaning' => 'สมดุล ความอดกลั้น'],
        ['name' => 'The Devil', 'name_th' => 'ปีศาจ', 'meaning' => 'กิเลส ข้อจำกัด'],
        ['name' => 'The Tower', 'name_th' => 'หอคอย', 'meaning' => 'การพังทลาย การตื่นรู้'],
        ['name' => 'The Star', 'name_th' => 'ดวงดาว', 'meaning' => 'ความหวัง แรงบันดาลใจ'],
        ['name' => 'The Moon', 'name_th' => 'พระจันทร์', 'meaning' => 'จิตใต้สำนึก ความกลัว'],
        ['name' => 'The Sun', 'name_th' => 'พระอาทิตย์', 'meaning' => 'ความสุข ความสำเร็จ'],
        ['name' => 'Judgement', 'name_th' => 'การตัดสิน', 'meaning' => 'การฟื้นคืน การตัดสินใจ'],
        ['name' => 'The World', 'name_th' => 'โลก', 'meaning' => 'ความสมบูรณ์ ความสำเร็จ'],
    ];

    public function getReadings(Request $request)
    {
        $readings = TarotReading::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json($readings);
    }

    public function createReading(Request $request)
    {
        $validated = $request->validate([
            'spread_type' => 'required|in:single,three_card,celtic_cross',
            'question' => 'nullable|string|max:500',
        ]);

        $cardCount = match ($validated['spread_type']) {
            'single' => 1,
            'three_card' => 3,
            'celtic_cross' => 10,
        };

        // Randomly select cards
        $selectedCards = collect($this->tarotCards)
            ->shuffle()
            ->take($cardCount)
            ->map(function ($card, $index) {
                $card['position'] = $index;
                $card['is_reversed'] = rand(0, 1) === 1;
                return $card;
            })
            ->values()
            ->toArray();

        // Generate interpretation
        $interpretation = $this->generateInterpretation($selectedCards, $validated['question'] ?? '');

        $reading = TarotReading::create([
            'id' => Str::uuid(),
            'user_id' => $request->user()->id,
            'spread_type' => $validated['spread_type'],
            'cards' => $selectedCards,
            'question' => $validated['question'] ?? null,
            'interpretation' => $interpretation,
        ]);

        return response()->json($reading, 201);
    }

    public function getReading(Request $request, $id)
    {
        $reading = TarotReading::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($reading);
    }

    private function generateInterpretation(array $cards, string $question): string
    {
        $cardNames = collect($cards)->pluck('name_th')->implode(', ');

        return "ไพ่ที่เปิดออกมา: {$cardNames}\n\nการตีความ: ไพ่ชุดนี้บ่งบอกถึงพลังงานและทิศทางในชีวิตของคุณ " .
               "ควรใช้สติปัญญาในการตัดสินใจและเปิดใจรับสิ่งใหม่ๆ ที่กำลังจะเข้ามา";
    }
}
