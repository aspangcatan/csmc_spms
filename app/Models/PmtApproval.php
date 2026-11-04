<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmtApproval extends Model
{
    protected $fillable = [
        'record_type',
        'record_id',
        'user_id',
        'signature',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
