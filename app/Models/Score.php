<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $guarded = [];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function judge()
    {
        return $this->belongsTo(Judge::class);
    }

    public function details()
    {
        return $this->hasMany(ScoreDetail::class);
    }
}