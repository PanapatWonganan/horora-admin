<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateReferral;
use App\Models\AffiliateWithdrawal;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    /**
     * Register as an affiliate
     */
    public function register(Request $request)
    {
        $user = $request->user();

        // Check if already registered
        if ($user->affiliate) {
            return response()->json([
                'message' => 'คุณเป็นตัวแทนอยู่แล้ว',
                'affiliate' => $user->affiliate,
            ], 409);
        }

        $validated = $request->validate([
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:20',
            'bank_account_name' => 'nullable|string|max:255',
            'promptpay_number' => 'nullable|string|max:15',
        ]);

        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'status' => 'active', // Auto-approve for now
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_name' => $validated['bank_account_name'] ?? null,
            'promptpay_number' => $validated['promptpay_number'] ?? null,
        ]);

        return response()->json([
            'message' => 'สมัครตัวแทนสำเร็จ',
            'affiliate' => $affiliate->load('user'),
        ], 201);
    }

    /**
     * Get affiliate dashboard data
     */
    public function dashboard(Request $request)
    {
        $affiliate = $this->getAffiliate($request);
        if (!$affiliate) {
            return response()->json(['message' => 'คุณยังไม่ได้เป็นตัวแทน'], 404);
        }

        // Recalculate stats
        $affiliate->recalculateBalance();
        $affiliate->updateTier();

        // Get recent commissions
        $recentCommissions = $affiliate->commissions()
            ->with('order.location', 'order.package')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Monthly stats
        $currentMonth = now()->format('Y-m');
        $monthlyEarnings = $affiliate->commissions()
            ->where('created_at', '>=', now()->startOfMonth())
            ->whereIn('status', ['approved', 'paid'])
            ->sum('commission_amount');

        $monthlyOrders = $affiliate->commissions()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        // Pending commissions (waiting for order to complete + holding period)
        $pendingCommissions = $affiliate->commissions()
            ->where('status', 'pending')
            ->sum('commission_amount');

        return response()->json([
            'affiliate' => $affiliate,
            'stats' => [
                'total_referrals' => $affiliate->total_referrals,
                'total_orders' => $affiliate->total_orders,
                'total_earned' => (float) $affiliate->total_commission_earned,
                'total_paid' => (float) $affiliate->total_commission_paid,
                'available_balance' => (float) $affiliate->available_balance,
                'pending_commission' => (float) $pendingCommissions,
                'monthly_earnings' => (float) $monthlyEarnings,
                'monthly_orders' => $monthlyOrders,
                'tier' => $affiliate->tier,
                'referral_code' => $affiliate->referral_code,
            ],
            'recent_commissions' => $recentCommissions,
        ]);
    }

    /**
     * Get commission history
     */
    public function commissions(Request $request)
    {
        $affiliate = $this->getAffiliate($request);
        if (!$affiliate) {
            return response()->json(['message' => 'คุณยังไม่ได้เป็นตัวแทน'], 404);
        }

        $commissions = $affiliate->commissions()
            ->with('order.location', 'order.package')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($commissions);
    }

    /**
     * Get referral link info
     */
    public function referralLink(Request $request)
    {
        $affiliate = $this->getAffiliate($request);
        if (!$affiliate) {
            return response()->json(['message' => 'คุณยังไม่ได้เป็นตัวแทน'], 404);
        }

        $baseUrl = config('app.url', 'https://horora-admin-production.up.railway.app');
        $referralCode = $affiliate->referral_code;
        $deepLink = "horora://merit?ref={$referralCode}";

        return response()->json([
            'referral_code' => $referralCode,
            'referral_link' => $deepLink,
            'deep_link' => $deepLink,
            'qr_data' => $deepLink,
            'tier' => $affiliate->tier,
        ]);
    }

    /**
     * Get referred users list
     */
    public function referredUsers(Request $request)
    {
        $affiliate = $this->getAffiliate($request);
        if (!$affiliate) {
            return response()->json(['message' => 'คุณยังไม่ได้เป็นตัวแทน'], 404);
        }

        $referrals = $affiliate->referrals()
            ->with('referredUser:id,name,email,created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($referrals);
    }

    /**
     * Request withdrawal
     */
    public function withdraw(Request $request)
    {
        $affiliate = $this->getAffiliate($request);
        if (!$affiliate) {
            return response()->json(['message' => 'คุณยังไม่ได้เป็นตัวแทน'], 404);
        }

        if (!$affiliate->isActive()) {
            return response()->json(['message' => 'บัญชีตัวแทนถูกระงับ'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:300',
            'method' => 'required|in:bank_transfer,promptpay',
        ]);

        // Check available balance
        $affiliate->recalculateBalance();

        if ($validated['amount'] > $affiliate->available_balance) {
            return response()->json([
                'message' => 'ยอดเงินไม่เพียงพอ',
                'available_balance' => (float) $affiliate->available_balance,
            ], 400);
        }

        // Determine bank details based on method
        $bankDetails = [];
        if ($validated['method'] === 'bank_transfer') {
            if (!$affiliate->bank_name || !$affiliate->bank_account_number) {
                return response()->json(['message' => 'กรุณาเพิ่มข้อมูลบัญชีธนาคารก่อน'], 400);
            }
            $bankDetails = [
                'bank_name' => $affiliate->bank_name,
                'bank_account_number' => $affiliate->bank_account_number,
                'bank_account_name' => $affiliate->bank_account_name,
            ];
        } else {
            if (!$affiliate->promptpay_number) {
                return response()->json(['message' => 'กรุณาเพิ่มเลขพร้อมเพย์ก่อน'], 400);
            }
            $bankDetails = [
                'promptpay_number' => $affiliate->promptpay_number,
            ];
        }

        $withdrawal = AffiliateWithdrawal::create([
            'affiliate_id' => $affiliate->id,
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            ...$bankDetails,
        ]);

        // Recalculate balance after withdrawal request
        $affiliate->recalculateBalance();

        return response()->json([
            'message' => 'ส่งคำขอถอนเงินสำเร็จ',
            'withdrawal' => $withdrawal,
            'available_balance' => (float) $affiliate->available_balance,
        ], 201);
    }

    /**
     * Get withdrawal history
     */
    public function withdrawals(Request $request)
    {
        $affiliate = $this->getAffiliate($request);
        if (!$affiliate) {
            return response()->json(['message' => 'คุณยังไม่ได้เป็นตัวแทน'], 404);
        }

        $withdrawals = $affiliate->withdrawals()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($withdrawals);
    }

    /**
     * Update bank/payment info
     */
    public function updatePaymentInfo(Request $request)
    {
        $affiliate = $this->getAffiliate($request);
        if (!$affiliate) {
            return response()->json(['message' => 'คุณยังไม่ได้เป็นตัวแทน'], 404);
        }

        $validated = $request->validate([
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:20',
            'bank_account_name' => 'nullable|string|max:255',
            'promptpay_number' => 'nullable|string|max:15',
        ]);

        $affiliate->update($validated);

        return response()->json([
            'message' => 'อัพเดทข้อมูลสำเร็จ',
            'affiliate' => $affiliate,
        ]);
    }

    /**
     * Track referral click (public endpoint)
     */
    public function trackReferral(Request $request)
    {
        $validated = $request->validate([
            'referral_code' => 'required|string|max:20',
            'source' => 'nullable|string|max:50',
        ]);

        $affiliate = Affiliate::where('referral_code', $validated['referral_code'])
            ->active()
            ->first();

        if (!$affiliate) {
            return response()->json(['message' => 'ไม่พบรหัสตัวแทน'], 404);
        }

        // Create referral record
        $referral = AffiliateReferral::create([
            'affiliate_id' => $affiliate->id,
            'referred_user_id' => $request->user()?->id,
            'referral_code' => $validated['referral_code'],
            'source' => $validated['source'] ?? 'link',
            'ip_address' => $request->ip(),
        ]);

        // Update affiliate stats
        $affiliate->increment('total_referrals');

        return response()->json([
            'message' => 'บันทึกการแนะนำสำเร็จ',
            'referral_id' => $referral->id,
        ]);
    }

    /**
     * Get affiliate status (for checking if user is already affiliate)
     */
    public function status(Request $request)
    {
        $affiliate = $request->user()->affiliate;

        return response()->json([
            'is_affiliate' => $affiliate !== null,
            'affiliate' => $affiliate,
        ]);
    }

    /**
     * Helper: Get current user's affiliate
     */
    private function getAffiliate(Request $request): ?Affiliate
    {
        return $request->user()->affiliate;
    }
}
