<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpcrLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'spcr_id',
        'subject',
        'content',
        'updated_by',
    ];

    public function spcr()
    {
        return $this->belongsTo(Spcr::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
