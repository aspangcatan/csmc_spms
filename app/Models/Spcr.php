<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spcr extends Model
{
    use HasFactory;

    protected $fillable = [
        'userid',
        'division_id',
        'year',
        'semester',
        'supervisor_id',
        'division_head_id',
        'pmt_id',
        'status',
        'final_average_rating',
        'final_rating',
        'final_rating_adjective',
        'core_dist',
        'support_dist',
        'strategic_dist',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }

    public function divisionHead()
    {
        return $this->belongsTo(User::class, 'division_head_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function pmt()
    {
        return $this->belongsTo(User::class, 'pmt_id');
    }

    public function entries()
    {
        return $this->hasMany(SpcrEntry::class);
    }

    public function coreEntries()
    {
        return $this->hasMany(SpcrEntry::class)->where('category', 'core');
    }

    public function supportEntries()
    {
        return $this->hasMany(SpcrEntry::class)->where('category', 'support');
    }

    public function strategicEntries()
    {
        return $this->hasMany(SpcrEntry::class)->where('category', 'strategic');
    }

    public function logs()
    {
        return $this->hasMany(SpcrLog::class)->orderBy('created_at', 'desc');
    }
}
