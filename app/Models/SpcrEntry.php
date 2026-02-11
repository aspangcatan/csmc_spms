<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpcrEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'spcr_id',
        'category',
        'output',
        'success_indicator',
        'accountability',
        'actual_accomplishment',
        'accomplishment_rate',
        'quantity_rating',
        'efficiency_rating',
        'timeliness_rating',
        'average_rating',
        'remarks',
    ];

    public function spcr()
    {
        return $this->belongsTo(Spcr::class);
    }
}
