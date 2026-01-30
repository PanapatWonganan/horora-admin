<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Merit Locations (สถานที่มงคล)
        Schema::create('merit_locations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name_th');
            $table->string('name_en')->nullable();
            $table->text('description')->nullable();
            $table->string('belief')->nullable(); // ความเชื่อ/ขอพรเรื่องอะไร
            $table->string('address')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Merit Packages (แพ็คเกจ)
        Schema::create('merit_packages', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name_th');
            $table->string('name_en')->nullable();
            $table->text('description')->nullable();
            $table->json('items')->nullable(); // รายการของที่ได้
            $table->decimal('price', 10, 2);
            $table->integer('photo_count')->default(3);
            $table->boolean('has_video')->default(false);
            $table->boolean('has_live')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Merit Orders (คำสั่งซื้อ)
        Schema::create('merit_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location_id');
            $table->string('package_id');

            // ข้อมูลผู้ขอพร
            $table->string('prayer_name');
            $table->date('prayer_birthdate')->nullable();
            $table->text('prayer_wish')->nullable();
            $table->string('prayer_phone')->nullable();

            // ราคาและการชำระเงิน
            $table->decimal('price', 10, 2);
            $table->string('slip_url')->nullable();
            $table->timestamp('paid_at')->nullable();

            // สถานะ
            $table->enum('status', ['pending', 'paid', 'processing', 'completed', 'cancelled'])->default('pending');

            // หลักฐานการไหว้
            $table->json('proof_urls')->nullable();
            $table->string('proof_video_url')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('merit_locations');
            $table->foreign('package_id')->references('id')->on('merit_packages');
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merit_orders');
        Schema::dropIfExists('merit_packages');
        Schema::dropIfExists('merit_locations');
    }
};
