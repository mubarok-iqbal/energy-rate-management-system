<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected $fillable = ['season_id', 'start_date', 'end_date'];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}
