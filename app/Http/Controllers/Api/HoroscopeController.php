<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HoroscopeHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HoroscopeController extends Controller
{
    public function getDailyHoroscope(Request $request)
    {
        $user = $request->user();
        $date = $request->get('date', now()->toDateString());
        $horoscopeType = $request->get('type', 'daily');
        $zodiacSign = $request->get('zodiac_sign', $user->thai_animal ?? 'มะโรง');

        // Check if we already have this horoscope cached
        $existing = HoroscopeHistory::where('user_id', $user->id)
            ->where('horoscope_date', $date)
            ->where('horoscope_type', $horoscopeType)
            ->first();

        if ($existing) {
            return response()->json($existing);
        }

        // Generate horoscope content (you can integrate with OpenAI here)
        $content = $this->generateHoroscopeContent($zodiacSign, $horoscopeType, $date);

        // Save to history
        $horoscope = HoroscopeHistory::create([
            'user_id' => $user->id,
            'horoscope_date' => $date,
            'horoscope_type' => $horoscopeType,
            'content' => $content,
            'zodiac_sign' => $zodiacSign,
        ]);

        return response()->json($horoscope);
    }

    public function getHistory(Request $request)
    {
        $user = $request->user();

        $horoscopes = HoroscopeHistory::where('user_id', $user->id)
            ->orderBy('horoscope_date', 'desc')
            ->limit(30)
            ->get();

        return response()->json($horoscopes);
    }

    private function generateHoroscopeContent(string $zodiacSign, string $type, string $date): array
    {
        // Default content - integrate with OpenAI for dynamic content
        $categories = ['love', 'career', 'finance', 'health'];
        $content = [];

        foreach ($categories as $category) {
            $content[$category] = [
                'score' => rand(60, 100),
                'description' => "ดวง{$category}สำหรับ{$zodiacSign}วันนี้ดี",
            ];
        }

        $content['overall'] = [
            'score' => rand(70, 95),
            'summary' => "ดวงชะตาโดยรวมของปี{$zodiacSign}สำหรับวันนี้",
            'lucky_color' => 'ม่วง',
            'lucky_number' => rand(1, 99),
        ];

        return $content;
    }
}
