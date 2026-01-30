<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->date('birth_date')->nullable();
            $table->time('birth_time')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('avatar_url')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->timestamp('subscription_end_date')->nullable();

            // Thai Zodiac fields
            $table->string('thai_animal')->nullable(); // ชวด, ฉลู, ขาล...
            $table->string('thai_year_name')->nullable(); // ปีชวด, ปีฉลู...
            $table->string('thai_element')->nullable(); // ทอง, น้ำ, ไม้, ไฟ, ดิน
            $table->string('thai_element_full')->nullable(); // ธาตุทอง, ธาตุน้ำ...

            // For admin
            $table->boolean('is_admin')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'birth_date', 'birth_time', 'birth_place',
                'avatar_url', 'is_premium', 'subscription_end_date',
                'thai_animal', 'thai_year_name', 'thai_element', 'thai_element_full',
                'is_admin'
            ]);
        });
    }
};
