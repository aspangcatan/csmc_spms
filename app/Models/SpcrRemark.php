<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpcrRemark extends Model
{
    use HasFactory;

    protected $fillable = [
        'spcr_entry_id',
        'content',
        'added_by',
    ];

    public function entry()
    {
        return $this->belongsTo(SpcrEntry::class, 'spcr_entry_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
