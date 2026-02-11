<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ipcr extends Model
{
    use HasFactory;

    protected $fillable = [
        'userid',
        'supervisor_id',
        'year',
        'semester',
        'section_head',
        'division_head',
        'highest_supervisor',
        'period_from',
        'period_to',
        'ipcr_date',
        'date_done',
        'comments',
        'status',
        'core_percentage_distribution',
        'support_percentage_distribution',
        'strategic_percentage_distribution',
        'final_average_rating',
        'final_rating',
        'final_rating_adjective',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function coreFunctions()
    {
        return $this->hasMany(IpcrCoreFunction::class, 'ipcr_id');
    }

    public function supportFunctions()
    {
        return $this->hasMany(IpcrSupportFunction::class, 'ipcr_id');
    }

    public function strategicFunctions()
    {
        return $this->hasMany(IpcrStrategicFunction::class, 'ipcr_id');
    }

    public function logs()
    {
        return $this->hasMany(IpcrLog::class, 'ipcr_id')->orderBy('created_at', 'desc');
    }
}
