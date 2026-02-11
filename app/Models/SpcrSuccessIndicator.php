<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpcrSuccessIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'spcr_entry_id',
        'target',
        'measures',
    ];

    public function entry()
    {
        return $this->belongsTo(SpcrEntry::class, 'spcr_entry_id');
    }
}
