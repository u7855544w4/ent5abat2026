<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Committee extends Model
{
    protected $fillable = ['name', 'description'];
    
    public function voters()
    {
        return $this->hasMany(Voter::class);
    }
}