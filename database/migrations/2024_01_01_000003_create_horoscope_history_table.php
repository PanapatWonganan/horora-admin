<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horoscope_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('thai_animal'); // ชวด, ฉลู, ขาล...
            $table->date('date');
            $table->text('content')->nullable();
            $table->tinyInteger('love_score')->nullable(); // 0-5
            $table->tinyInteger('career_score')->nullable(); // 0-5
            $table->tinyInteger('health_score')->nullable(); // 0-5
            $table->string('lucky_number')->nullable();
            $table->string('lucky_color')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index('thai_animal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horoscope_history');
    }
};
