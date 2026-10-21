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
        Schema::create('challenge_songs', function (Blueprint $table) {
            $table->id();
            $table->string('genre'); // POP, ROCK, DANGDUT, NUSANTARA MIX, SKA/REGGAE
            $table->string('title');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_used')->default(false);
            $table->string('used_by')->nullable();
            $table->timestamps();
        });

        Schema::create('challenge_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('participant_id')->nullable();
            $table->string('participant_name');
            $table->string('genre');
            $table->tinyInteger('genre_attempt')->default(1);
            $table->string('genre_rejected')->nullable();
            $table->string('song_title');
            $table->tinyInteger('song_attempt')->default(1);
            $table->string('song_rejected')->nullable();
            $table->timestamps();
        });

        // Seed 35 initial songs
        $now = now();
        $songs = [
            // POP (15)
            ['genre' => 'POP', 'title' => 'Kulakukan Semua Untukmu - RAN'],
            ['genre' => 'POP', 'title' => 'Alamak - Rizky Febrian'],
            ['genre' => 'POP', 'title' => 'Pudar - Rosa'],
            ['genre' => 'POP', 'title' => 'Kamu - Coboy Junior'],
            ['genre' => 'POP', 'title' => 'Harus Bahagia - Yura Yunita'],
            ['genre' => 'POP', 'title' => 'Salah - Potret'],
            ['genre' => 'POP', 'title' => 'Cerita Cinta - Kahitna'],
            ['genre' => 'POP', 'title' => 'Kala Cinta Menggoda - Ariel Noah'],
            ['genre' => 'POP', 'title' => 'Dahulu - the groove'],
            ['genre' => 'POP', 'title' => 'Cenat cenut - Smash'],
            ['genre' => 'POP', 'title' => 'Bebas - Iwa K'],
            ['genre' => 'POP', 'title' => 'Aku wanita - Reza Artamevia'],
            ['genre' => 'POP', 'title' => 'Kita - Sheila on 7'],
            ['genre' => 'POP', 'title' => 'Gejolak kawula muda - Club 80s'],
            ['genre' => 'POP', 'title' => 'Kamulah satu-satunya - Dewa 19'],

            // ROCK (7)
            ['genre' => 'ROCK', 'title' => 'Ningrat - Jamrud'],
            ['genre' => 'ROCK', 'title' => 'Racun - The Changcuters'],
            ['genre' => 'ROCK', 'title' => 'Pangeran cinta - Dewa 19'],
            ['genre' => 'ROCK', 'title' => 'Ceria - Jrock'],
            ['genre' => 'ROCK', 'title' => 'Cukup Siti Nurbaya - Dewa 19'],
            ['genre' => 'ROCK', 'title' => 'Papa rock n roll - The Dance Company'],
            ['genre' => 'ROCK', 'title' => 'Jika kami bersama - Superman is Dead'],

            // DANGDUT (5)
            ['genre' => 'DANGDUT', 'title' => 'Seperti Mati Lampu - King Nassar'],
            ['genre' => 'DANGDUT', 'title' => 'Bang Jono - Zaskia Gotik'],
            ['genre' => 'DANGDUT', 'title' => 'Sambalado - Ayu Ting-Ting'],
            ['genre' => 'DANGDUT', 'title' => 'Meriang - Cita Citata'],
            ['genre' => 'DANGDUT', 'title' => 'Pacar Lima Langkah - Iceu Wong'],

            // NUSANTARA MIX (4)
            ['genre' => 'NUSANTARA MIX', 'title' => 'Tor Monitor Ketua - Ecko Show, Juan Reza, Chesylino'],
            ['genre' => 'NUSANTARA MIX', 'title' => 'Koyo Jogja Istimewa - Ndarboy Gen'],
            ['genre' => 'NUSANTARA MIX', 'title' => 'Tresno Tekane Mati - NDX A.K.A'],
            ['genre' => 'NUSANTARA MIX', 'title' => 'Los Dol - Denny Caknan'],

            // SKA/REGGAE (4)
            ['genre' => 'SKA/REGGAE', 'title' => 'Sakit Hati - Tipe X'],
            ['genre' => 'SKA/REGGAE', 'title' => 'Hey Cantik - Shaggy Dog'],
            ['genre' => 'SKA/REGGAE', 'title' => 'Genit - Tipe X'],
            ['genre' => 'SKA/REGGAE', 'title' => 'Salam Rindu - Tipe X'],
        ];

        $insertData = array_map(function ($song) use ($now) {
            return [
                'genre' => $song['genre'],
                'title' => $song['title'],
                'is_active' => true,
                'is_used' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $songs);

        DB::table('challenge_songs')->insert($insertData);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_results');
        Schema::dropIfExists('challenge_songs');
    }
};
