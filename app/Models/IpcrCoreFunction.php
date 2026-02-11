<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpcrCoreFunction extends Model
{
    use HasFactory;

    protected $fillable = [
        'ipcr_id',
        'output',
        'success_indicator',
        'actual_accomplishment',
        'quantity_rating',
        'efficiency_rating',
        'timeliness_rating',
        'average_rating',
        'remarks',
    ];

    public function ipcr()
    {
        return $this->belongsTo(Ipcr::class);
    }
}
