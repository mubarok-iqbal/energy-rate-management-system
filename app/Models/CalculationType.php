<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalculationType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public const FIX_PER_DAY = 1;
    public const FIX_PER_MONTH = 2;
    public const PERIOD = 3;
}
