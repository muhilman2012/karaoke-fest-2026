<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('judges', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Misal: Juri 1, Juri 2
            $table->string('passcode', 4)->unique(); // PIN 4 digit untuk login di tablet
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('judges');
    }
};
