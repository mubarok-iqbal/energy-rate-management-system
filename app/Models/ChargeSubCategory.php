<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargeSubCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function chargeCategory()
    {
        return $this->belongsTo(ChargeCategory::class);
    }
}
