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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('judge_id')->constrained()->cascadeOnDelete();
            
            // Hanya menyimpan total akhir dari gabungan persentase
            $table->decimal('total_score', 5, 2)->default(0); 
            
            $table->timestamps();
            $table->unique(['participant_id', 'judge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
