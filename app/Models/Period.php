<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use HasFactory;

    public function periodDetails()
    {
        return $this->hasMany(PeriodDetail::class);
    }
}
