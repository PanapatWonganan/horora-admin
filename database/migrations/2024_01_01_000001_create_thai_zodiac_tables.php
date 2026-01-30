<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Thai Zodiac Animals (ปีนักษัตร)
        Schema::create('thai_zodiac_animals', function (Blueprint $table) {
            $table->id();
            $table->string('animal_name')->unique(); // ชวด, ฉลู, ขาล...
            $table->string('thai_name'); // ปีชวด, ปีฉลู...
            $table->string('english_name');
            $table->text('characteristics');
            $table->text('compatibility')->nullable();
            $table->json('lucky_numbers')->nullable();
            $table->timestamps();
        });

        // Thai Elements (ธาตุ)
        Schema::create('thai_elements', function (Blueprint $table) {
            $table->id();
            $table->string('element_name')->unique(); // ทอง, น้ำ, ไม้, ไฟ, ดิน
            $table->string('element_full'); // ธาตุทอง, ธาตุน้ำ...
            $table->json('lucky_colors')->nullable();
            $table->text('characteristics')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thai_elements');
        Schema::dropIfExists('thai_zodiac_animals');
    }
};
