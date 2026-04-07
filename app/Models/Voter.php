<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voter extends Model
{
    protected $fillable = [
        'full_name', 
        'family_name', 
        'electoral_code', 
        'electoral_center', 
        'status',
        'committee_id',
        'follower_name',
        'follow_date',
        'notes'
    ];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }
}