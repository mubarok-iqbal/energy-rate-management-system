<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_plan_id',
        'name',
        'description',
        'is_active',
    ];

    public function ratePlan()
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function subChargeCategories()
    {
        return $this->hasMany(SubChargeCategory::class);
    }
}
