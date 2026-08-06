<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spinwheel_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('prize')->nullable();
            $table->integer('win_probability')->default(1); 
            $table->boolean('is_winner')->default(false);
            $table->timestamp('won_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spinwheel_items');
    }
};