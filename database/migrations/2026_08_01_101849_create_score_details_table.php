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
        Schema::create('score_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('score_id')->constrained()->cascadeOnDelete();
            $table->foreignId('aspect_id')->constrained()->cascadeOnDelete();
            $table->integer('score_value'); // Nilai mentah (1-100) dari juri
            $table->decimal('weighted_score', 5, 2); // Nilai setelah dikali persentase aspek
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_details');
    }
};
