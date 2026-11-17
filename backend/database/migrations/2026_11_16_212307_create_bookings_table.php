<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');

            // Отмена хранится мягко: запись остаётся в истории.
            $table->timestamp('cancelled_at')->nullable();

            // Равно slot_id, пока бронь активна, и NULL после отмены.
            // UNIQUE-индекс гарантирует на уровне БД не более одной активной
            // брони на слот (NULL-значения в UNIQUE не конфликтуют между собой).
            // Работает одинаково в SQLite, MySQL и PostgreSQL.
            $table->unsignedBigInteger('active_slot_id')->nullable()->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
