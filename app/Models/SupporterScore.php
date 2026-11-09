<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupporterScore extends Model
{
    protected $guarded = [];

    protected $casts = [
        'score' => 'integer',
        'is_locked' => 'boolean',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function judge()
    {
        return $this->belongsTo(SupporterJudge::class, 'supporter_judge_id');
    }
}
