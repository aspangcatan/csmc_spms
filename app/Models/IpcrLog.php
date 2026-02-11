<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpcrLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ipcr_id',
        'subject',
        'content',
        'updated_by',
    ];

    public function ipcr()
    {
        return $this->belongsTo(Ipcr::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
