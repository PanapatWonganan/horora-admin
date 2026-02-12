<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Affiliates (ตัวแทน)
        Schema::create('affiliates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('referral_code', 20)->unique(); // ABC123
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->enum('status', ['pending', 'active', 'suspended'])->default('pending');

            // Bank info for withdrawal
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();

            // PromptPay
            $table->string('promptpay_number')->nullable();

            // Stats (cached for quick access)
            $table->integer('total_referrals')->default(0);
            $table->integer('total_orders')->default(0);
            $table->decimal('total_commission_earned', 12, 2)->default(0);
            $table->decimal('total_commission_paid', 12, 2)->default(0);
            $table->decimal('available_balance', 12, 2)->default(0);

            // Current month tracking for tier
            $table->integer('monthly_orders')->default(0);
            $table->string('monthly_period')->nullable(); // e.g. "2026-02"

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('referral_code');
            $table->index('status');
            $table->index('user_id');
        });

        // Affiliate Referrals (ติดตามว่าใครมาจาก affiliate ไหน)
        Schema::create('affiliate_referrals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('affiliate_id');
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('referral_code', 20);
            $table->string('source')->nullable(); // link, qr, share
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->foreign('affiliate_id')->references('id')->on('affiliates')->cascadeOnDelete();
            $table->index('affiliate_id');
            $table->index('referred_user_id');
        });

        // Affiliate Commissions (ค่าคอมมิชชั่นแต่ละรายการ)
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('affiliate_id');
            $table->uuid('order_id');
            $table->uuid('referral_id')->nullable();

            $table->decimal('order_amount', 10, 2);
            $table->decimal('commission_rate', 5, 2); // percentage e.g. 15.00
            $table->decimal('commission_amount', 10, 2);
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');

            // pending until order completed + 7 days holding
            $table->timestamp('available_at')->nullable();
            $table->timestamps();

            $table->foreign('affiliate_id')->references('id')->on('affiliates')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('merit_orders')->cascadeOnDelete();
            $table->foreign('referral_id')->references('id')->on('affiliate_referrals')->nullOnDelete();
            $table->index('affiliate_id');
            $table->index('order_id');
            $table->index('status');
        });

        // Affiliate Withdrawals (คำขอถอนเงิน)
        Schema::create('affiliate_withdrawals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('affiliate_id');

            $table->decimal('amount', 12, 2);
            $table->enum('method', ['bank_transfer', 'promptpay'])->default('bank_transfer');

            // Bank details snapshot at time of withdrawal
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('promptpay_number')->nullable();

            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');
            $table->string('transfer_slip_url')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('affiliate_id')->references('id')->on('affiliates')->cascadeOnDelete();
            $table->index('affiliate_id');
            $table->index('status');
        });

        // Add referral tracking columns to merit_orders
        Schema::table('merit_orders', function (Blueprint $table) {
            $table->string('referral_code', 20)->nullable()->after('admin_note');
            $table->uuid('affiliate_id')->nullable()->after('referral_code');

            $table->foreign('affiliate_id')->references('id')->on('affiliates')->nullOnDelete();
            $table->index('referral_code');
        });

        // Commission rate settings
        Schema::create('affiliate_commission_rates', function (Blueprint $table) {
            $table->id();
            $table->string('package_id');
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum']);
            $table->decimal('rate', 5, 2); // percentage
            $table->timestamps();

            $table->foreign('package_id')->references('id')->on('merit_packages');
            $table->unique(['package_id', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::table('merit_orders', function (Blueprint $table) {
            $table->dropForeign(['affiliate_id']);
            $table->dropIndex(['referral_code']);
            $table->dropColumn(['referral_code', 'affiliate_id']);
        });

        Schema::dropIfExists('affiliate_commission_rates');
        Schema::dropIfExists('affiliate_withdrawals');
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('affiliate_referrals');
        Schema::dropIfExists('affiliates');
    }
};
