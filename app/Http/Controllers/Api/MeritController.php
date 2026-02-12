<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\MeritLocation;
use App\Models\MeritOrder;
use App\Models\MeritPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MeritController extends Controller
{
    public function getLocations()
    {
        $locations = MeritLocation::active()
            ->ordered()
            ->get();

        return response()->json($locations);
    }

    public function getLocation($id)
    {
        $location = MeritLocation::with('packages')
            ->findOrFail($id);

        return response()->json($location);
    }

    public function getPackages(Request $request)
    {
        $locationId = $request->get('location_id');

        $query = MeritPackage::active()->ordered();

        // If location has specific packages, filter by them
        // For now, return all active packages

        return response()->json($query->get());
    }

    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:merit_locations,id',
            'package_id' => 'required|exists:merit_packages,id',
            'prayer_name' => 'required|string|max:255',
            'prayer_birthdate' => 'nullable|date',
            'prayer_wish' => 'nullable|string',
            'prayer_phone' => 'nullable|string|max:20',
            'referral_code' => 'nullable|string|max:20',
        ]);

        $package = MeritPackage::findOrFail($validated['package_id']);

        // Resolve affiliate from referral code
        $affiliateId = null;
        $referralCode = $validated['referral_code'] ?? null;
        if ($referralCode) {
            $affiliate = Affiliate::where('referral_code', $referralCode)
                ->active()
                ->first();
            // Prevent self-referral
            if ($affiliate && $affiliate->user_id !== $request->user()->id) {
                $affiliateId = $affiliate->id;
            } else {
                $referralCode = null; // Clear invalid code
            }
        }

        $order = MeritOrder::create([
            'id' => Str::uuid(),
            'order_number' => 'MO-' . strtoupper(Str::random(8)),
            'user_id' => $request->user()->id,
            'location_id' => $validated['location_id'],
            'package_id' => $validated['package_id'],
            'prayer_name' => $validated['prayer_name'],
            'prayer_birthdate' => $validated['prayer_birthdate'] ?? null,
            'prayer_wish' => $validated['prayer_wish'] ?? null,
            'prayer_phone' => $validated['prayer_phone'] ?? null,
            'price' => $package->price,
            'status' => 'pending',
            'referral_code' => $referralCode,
            'affiliate_id' => $affiliateId,
        ]);

        // Create pending commission if affiliate exists
        if ($affiliateId) {
            $this->createAffiliateCommission($order, $affiliate);
        }

        return response()->json($order->load(['location', 'package']), 201);
    }

    public function uploadSlip(Request $request, $orderId)
    {
        $request->validate([
            'slip' => 'required|image|max:5120', // 5MB max
        ]);

        $order = MeritOrder::where('user_id', $request->user()->id)
            ->findOrFail($orderId);

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'ไม่สามารถอัพโหลดสลิปได้'], 400);
        }

        $path = $request->file('slip')->store('slips', 'public');

        $order->update([
            'slip_url' => $path,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json($order);
    }

    public function getMyOrders(Request $request)
    {
        $orders = MeritOrder::where('user_id', $request->user()->id)
            ->with(['location', 'package'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function getOrder(Request $request, $orderId)
    {
        $order = MeritOrder::where('user_id', $request->user()->id)
            ->with(['location', 'package'])
            ->findOrFail($orderId);

        return response()->json($order);
    }

    public function getWeeklySchedule()
    {
        // Get this week's schedule based on locations
        $locations = MeritLocation::active()
            ->ordered()
            ->get()
            ->map(function ($location, $index) {
                $dayOfWeek = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
                return [
                    'day' => $dayOfWeek[$index % 7],
                    'location' => $location,
                ];
            });

        return response()->json($locations);
    }

    /**
     * Create order from weekly schedule (no strict location/package validation)
     */
    public function createWeeklyOrder(Request $request)
    {
        $validated = $request->validate([
            'location_name' => 'required|string|max:255',
            'package_name' => 'required|string|max:255',
            'prayer_name' => 'required|string|max:255',
            'prayer_birthdate' => 'nullable|date',
            'prayer_wish' => 'nullable|string',
            'prayer_phone' => 'nullable|string|max:20',
            'price' => 'required|numeric',
        ]);

        // Try to find matching location or use first one as fallback
        $location = MeritLocation::where('name_th', 'like', '%' . $validated['location_name'] . '%')->first()
            ?? MeritLocation::first();
        $package = MeritPackage::where('name_th', 'like', '%' . $validated['package_name'] . '%')->first()
            ?? MeritPackage::first();

        $order = MeritOrder::create([
            'id' => Str::uuid(),
            'order_number' => 'MW-' . strtoupper(Str::random(8)),
            'user_id' => $request->user()?->id,
            'location_id' => $location->id,
            'package_id' => $package->id,
            'prayer_name' => $validated['prayer_name'],
            'prayer_birthdate' => $validated['prayer_birthdate'] ?? null,
            'prayer_wish' => $validated['prayer_wish'] ?? null,
            'prayer_phone' => $validated['prayer_phone'] ?? null,
            'price' => $validated['price'],
            'status' => 'pending',
            // Store raw location/package names for reference
            'admin_note' => "สถานที่: {$validated['location_name']}\nแพ็คเกจ: {$validated['package_name']}",
        ]);

        return response()->json($order->load(['location', 'package']), 201);
    }

    /**
     * Create affiliate commission for an order
     */
    private function createAffiliateCommission(MeritOrder $order, Affiliate $affiliate): void
    {
        $rate = $affiliate->getCommissionRate($order->package_id);
        $commissionAmount = round($order->price * ($rate / 100), 2);

        AffiliateCommission::create([
            'affiliate_id' => $affiliate->id,
            'order_id' => $order->id,
            'order_amount' => $order->price,
            'commission_rate' => $rate,
            'commission_amount' => $commissionAmount,
            'status' => 'pending', // Will be approved when order is completed
            'available_at' => null, // Set when order completes + 7 days
        ]);

        // Update affiliate stats
        $affiliate->increment('total_orders');

        // Update monthly tracking
        $currentPeriod = now()->format('Y-m');
        if ($affiliate->monthly_period !== $currentPeriod) {
            $affiliate->update([
                'monthly_orders' => 1,
                'monthly_period' => $currentPeriod,
            ]);
        } else {
            $affiliate->increment('monthly_orders');
        }

        // Update tier
        $affiliate->updateTier();
    }

    /**
     * Upload slip for weekly order (no auth required)
     */
    public function uploadWeeklySlip(Request $request, $orderId)
    {
        $request->validate([
            'slip' => 'required|image|max:5120', // 5MB max
        ]);

        $order = MeritOrder::findOrFail($orderId);

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'ไม่สามารถอัพโหลดสลิปได้'], 400);
        }

        $path = $request->file('slip')->store('slips', 'public');

        $order->update([
            'slip_url' => $path,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Send notification (you can add Telegram notification here)
        // $this->sendTelegramNotification($order);

        return response()->json($order);
    }
}
