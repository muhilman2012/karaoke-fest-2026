<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('challenge_participants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('order_number')->default(0);
            $table->string('genre')->nullable();
            $table->string('song_title')->nullable();
            $table->tinyInteger('attempts_used')->default(1); // 1 = terima di putaran 1, 2 = spin ulang putaran 2
            $table->string('round1_genre')->nullable();
            $table->string('round1_song')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'in_progress', 'completed'
            $table->timestamps();
        });

        // Seed daftar peserta dari list Jotform KORPRI
        $participants = [
            'Sekretariat Wakil Presiden',
            'Sekretariat Militer Presiden',
            'Sekretariat Dukungan Kabinet',
            'Sekretariat Dewan Pertimbangan Presiden RI',
            'Sekretariat Kantor Staf Presiden',
            'Dewan Ekonomi Nasional',
            'Bakom RI',
            'Bappisus',
            'PPK Gelora Bung Karno',
            'PPK Kemayoran',
            'Pengelola Taman Mini Indonesia Indah',
        ];

        $now = now();
        $insertData = [];
        foreach ($participants as $index => $name) {
            $insertData[] = [
                'name' => $name,
                'order_number' => $index + 1,
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('challenge_participants')->insert($insertData);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_participants');
    }
};
