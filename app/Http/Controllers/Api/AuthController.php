<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'birth_date' => 'nullable|date',
            'birth_time' => 'nullable|string',
            'birth_place' => 'nullable|string',
        ]);

        $thaiZodiac = [];
        if (!empty($validated['birth_date'])) {
            $year = date('Y', strtotime($validated['birth_date']));
            $thaiZodiac = User::calculateThaiZodiac((int)$year);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'birth_date' => $validated['birth_date'] ?? null,
            'birth_time' => $validated['birth_time'] ?? null,
            'birth_place' => $validated['birth_place'] ?? null,
            'thai_animal' => $thaiZodiac['animal'] ?? null,
            'thai_year_name' => $thaiZodiac['year_name'] ?? null,
            'thai_element' => $thaiZodiac['element'] ?? null,
            'thai_element_full' => $thaiZodiac['element_full'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($validated)) {
            throw ValidationException::withMessages([
                'email' => ['อีเมลหรือรหัสผ่านไม่ถูกต้อง'],
            ]);
        }

        $user = User::where('email', $validated['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'ออกจากระบบสำเร็จ']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'birth_time' => 'nullable|string',
            'birth_place' => 'nullable|string',
            'avatar_url' => 'nullable|string',
        ]);

        // Recalculate Thai zodiac if birth date changed
        if (isset($validated['birth_date']) && $validated['birth_date'] !== $user->birth_date) {
            $year = date('Y', strtotime($validated['birth_date']));
            $thaiZodiac = User::calculateThaiZodiac((int)$year);
            $validated['thai_animal'] = $thaiZodiac['animal'];
            $validated['thai_year_name'] = $thaiZodiac['year_name'];
            $validated['thai_element'] = $thaiZodiac['element'];
            $validated['thai_element_full'] = $thaiZodiac['element_full'];
        }

        $user->update($validated);

        return response()->json($user);
    }
}
