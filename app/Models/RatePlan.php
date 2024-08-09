<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'retail_id',
        'name',
        'is_active',
        'description',
    ];

    public function retail()
    {
        return $this->belongsTo(Retail::class);
    }

    public function chargeCategories()
    {
        return $this->hasMany(ChargeCategory::class);
    }
}
