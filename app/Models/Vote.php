<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = ['participant_id', 'device_token', 'ip_address'];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}