<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supporter_judges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('affiliation')->nullable();
            $table->string('passcode', 4)->unique();
            $table->timestamps();
        });

        Schema::create('supporter_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supporter_judge_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->unique(['participant_id', 'supporter_judge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supporter_scores');
        Schema::dropIfExists('supporter_judges');
    }
};
