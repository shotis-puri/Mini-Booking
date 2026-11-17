<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamps();

            // Защита сидера от дубликатов: один слот на одно время начала.
            $table->unique('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
