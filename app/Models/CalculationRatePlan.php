<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalculationRatePlan extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function calculations()
    {
        return $this->hasMany(Calculation::class);
    }

    public function ratePlan()
    {
        return $this->belongsTo(RatePlan::class);
    }
}
