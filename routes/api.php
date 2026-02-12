<?php

use App\Http\Controllers\Api\AffiliateController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\HoroscopeController;
use App\Http\Controllers\Api\MeritController;
use App\Http\Controllers\Api\TarotController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Public merit data
Route::get('/merit/locations', [MeritController::class, 'getLocations']);
Route::get('/merit/locations/{id}', [MeritController::class, 'getLocation']);
Route::get('/merit/packages', [MeritController::class, 'getPackages']);
Route::get('/merit/schedule', [MeritController::class, 'getWeeklySchedule']);

// Public weekly merit orders (flexible validation for weekly schedule flow)
Route::post('/merit/weekly-orders', [MeritController::class, 'createWeeklyOrder']);
Route::post('/merit/weekly-orders/{id}/slip', [MeritController::class, 'uploadWeeklySlip']);

// Public affiliate tracking
Route::post('/affiliate/track', [AffiliateController::class, 'trackReferral']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

    // Horoscope
    Route::get('/horoscope/daily', [HoroscopeController::class, 'getDailyHoroscope']);
    Route::get('/horoscope/history', [HoroscopeController::class, 'getHistory']);

    // Merit orders
    Route::post('/merit/orders', [MeritController::class, 'createOrder']);
    Route::get('/merit/orders', [MeritController::class, 'getMyOrders']);
    Route::get('/merit/orders/{id}', [MeritController::class, 'getOrder']);
    Route::post('/merit/orders/{id}/slip', [MeritController::class, 'uploadSlip']);

    // Chat
    Route::get('/chat/sessions', [ChatController::class, 'getSessions']);
    Route::post('/chat/sessions', [ChatController::class, 'createSession']);
    Route::get('/chat/sessions/{id}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/sessions/{id}/messages', [ChatController::class, 'sendMessage']);
    Route::delete('/chat/sessions/{id}', [ChatController::class, 'deleteSession']);

    // Tarot
    Route::get('/tarot/readings', [TarotController::class, 'getReadings']);
    Route::post('/tarot/readings', [TarotController::class, 'createReading']);
    Route::get('/tarot/readings/{id}', [TarotController::class, 'getReading']);

    // Affiliate
    Route::prefix('affiliate')->group(function () {
        Route::get('/status', [AffiliateController::class, 'status']);
        Route::post('/register', [AffiliateController::class, 'register']);
        Route::get('/dashboard', [AffiliateController::class, 'dashboard']);
        Route::get('/commissions', [AffiliateController::class, 'commissions']);
        Route::get('/referral-link', [AffiliateController::class, 'referralLink']);
        Route::get('/referred-users', [AffiliateController::class, 'referredUsers']);
        Route::post('/withdraw', [AffiliateController::class, 'withdraw']);
        Route::get('/withdrawals', [AffiliateController::class, 'withdrawals']);
        Route::put('/payment-info', [AffiliateController::class, 'updatePaymentInfo']);
    });
});
