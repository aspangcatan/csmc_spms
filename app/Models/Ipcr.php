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
        'pmt_id',
        'unit_head_reviewed_by',
        'unit_head_reviewed_at',
        'unit_head_review_remarks',
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

    protected $casts = [
        'unit_head_reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function divisionHead()
    {
        return $this->belongsTo(User::class, 'division_head');
    }

    public function highestSupervisor()
    {
        return $this->belongsTo(User::class, 'highest_supervisor');
    }

    public function pmt()
    {
        return $this->belongsTo(User::class, 'pmt_id');
    }

    public function unitHeadReviewer()
    {
        return $this->belongsTo(User::class, 'unit_head_reviewed_by');
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
