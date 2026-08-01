<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreDetail extends Model
{
    // Mengizinkan semua kolom untuk diisi
    protected $guarded = [];

    // (Opsional) Relasi balik ke Score dan Aspect
    public function score()
    {
        return $this->belongsTo(Score::class);
    }

    public function aspect()
    {
        return $this->belongsTo(Aspect::class);
    }
}