<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpcrRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'spcr_entry_id',
        'quantity',
        'efficiency',
        'timeliness',
        'average',
        'rated_by',
    ];

    public function entry()
    {
        return $this->belongsTo(SpcrEntry::class, 'spcr_entry_id');
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rated_by');
    }
}
