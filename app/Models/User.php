<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    protected $connection = 'user';
    protected $table = 'users';
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fname',
        'mname',
        'lname',
        'suffix',
        'email',
        'password',
        'section',
        'division',
        'designation',
        'picture',
        'title'
    ];

    protected $appends = [
        'name',
        'designation_name',
        'section_name',
        'division_name',
        'profile_photo_url',
    ];

    public function getNameAttribute()
    {
        return trim("{$this->fname} {$this->mname} {$this->lname} {$this->suffix}");
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->picture 
            ? "https://dohcsmc.com/id/storage/crop/{$this->picture}" 
            : "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&background=f06a38&color=fff";
    }

    public function getDesignationNameAttribute()
    {
        if (!$this->designation) return '';
        
        try {
            return \Illuminate\Support\Facades\DB::connection('user')
                ->table('designation')
                ->where('id', $this->designation)
                ->value('description') ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getSectionNameAttribute()
    {
        if (!$this->section) return '';
        try {
            return \Illuminate\Support\Facades\DB::connection('user')
                ->table('section')
                ->where('id', $this->section)
                ->value('description') ?? '';
        } catch (\Exception $e) { return ''; }
    }

      public function getSectionAcronymAttribute()
    {
        if (!$this->section) return '';
        try {
            return \Illuminate\Support\Facades\DB::connection('user')
                ->table('section')
                ->where('id', $this->section)
                ->value('code') ?? '';
        } catch (\Exception $e) { return ''; }
    }

    public function getDivisionNameAttribute()
    {
        if (!$this->division) return '';
        try {
            return \Illuminate\Support\Facades\DB::connection('user')
                ->table('division')
                ->where('id', $this->division)
                ->value('description') ?? '';
        } catch (\Exception $e) { return ''; }
    }

          public function getDivisionAcronymAttribute()
    {
        if (!$this->section) return '';
        try {
            return \Illuminate\Support\Facades\DB::connection('user')
                ->table('division')
                ->where('id', $this->division)
                ->value('code') ?? '';
        } catch (\Exception $e) { return ''; }
    }

    public function getHighestSupervisor(): string
    {
        try {
            $user = \Illuminate\Support\Facades\DB::connection('user')
                ->table('users')
                ->where('id', 35)
                ->first(['fname', 'mname', 'lname', 'suffix', 'title']);

            if (!$user) return '';

            return trim(
                ($user->fname ?? '') . ' ' .
                (!empty($user->mname) ? substr($user->mname, 0, 1) . '. ' : '') .
                ($user->lname ?? '') .
                (!empty($user->suffix) ? ' ' . $user->suffix : '') .
                (!empty($user->title) ? ', ' . $user->title : '')
            );
        } catch (\Exception $e) {
            return '';
        }
    }

    public function isSupervisor()
    {
        return \Illuminate\Support\Facades\DB::connection('user')->table('section')->where('head', $this->id)->exists() ||
               \Illuminate\Support\Facades\DB::connection('user')->table('division')->where('head', $this->id)->exists();
    }

    // Heads any section, whether a main section or a unit.
    public function headsAnySection(): bool
    {
        return \Illuminate\Support\Facades\DB::connection('user')->table('section')->where('head', $this->id)->exists();
    }

    // Heads a main section (no parent section).
    public function isSectionHead(): bool
    {
        return \Illuminate\Support\Facades\DB::connection('user')
            ->table('section')
            ->where('head', $this->id)
            ->where(function ($query) {
                $query->whereNull('subsection')
                    ->orWhereIn('subsection', ['', '0']);
            })
            ->exists();
    }

    // Heads a unit (a section that has a parent section).
    public function isUnitHead(): bool
    {
        return \Illuminate\Support\Facades\DB::connection('user')
            ->table('section')
            ->where('head', $this->id)
            ->whereNotNull('subsection')
            ->whereNotIn('subsection', ['', '0'])
            ->exists();
    }

    public function isDivisionHead()
    {
        return \Illuminate\Support\Facades\DB::connection('user')->table('division')->where('head', $this->id)->exists();
    }

    public function hasChildUnits(): bool
    {
        $managedSectionIds = \Illuminate\Support\Facades\DB::connection('user')
            ->table('section')
            ->where('head', $this->id)
            ->pluck('id')
            ->toArray();

        if (empty($managedSectionIds)) {
            return false;
        }

        return \Illuminate\Support\Facades\DB::connection('user')
            ->table('section')
            ->whereIn('subsection', $managedSectionIds)
            ->exists();
    }

    public function canAccessSpcrStaff(): bool
    {
        return $this->hasAdminAccessRight() || $this->isDivisionHead() || ($this->isSectionHead() && $this->hasChildUnits());
    }

    public function canAccessStaffIpcr(): bool
    {
        return $this->hasAdminAccessRight() || $this->isSupervisor();
    }

    // True when $ownerId falls under this user's hierarchy:
    // their division (division head) or their section and its child units (section/unit head).
    public function managesUser($ownerId): bool
    {
        if ((int) $ownerId === (int) $this->id) {
            return false;
        }

        $owner = self::find($ownerId);
        if (!$owner) {
            return false;
        }

        $db = \Illuminate\Support\Facades\DB::connection('user');

        if ($owner->division && $db->table('division')->where('head', $this->id)->where('id', $owner->division)->exists()) {
            return true;
        }

        $sectionIds = $db->table('section')->where('head', $this->id)->pluck('id')->toArray();
        if (empty($sectionIds)) {
            return false;
        }

        $sectionIds = array_merge($sectionIds, $db->table('section')->whereIn('subsection', $sectionIds)->pluck('id')->toArray());

        return in_array($owner->section, $sectionIds);
    }

    public function canViewIpcr(Ipcr $ipcr): bool
    {
        return (int) $ipcr->userid === (int) $this->id
            || $this->hasAdminAccessRight()
            || $this->isPmt()
            || in_array((int) $this->id, array_map('intval', [$ipcr->supervisor_id, $ipcr->division_head, $ipcr->highest_supervisor, $ipcr->pmt_id]), true)
            || $this->managesUser($ipcr->userid);
    }

    // Editing is limited to the owner and the approvers assigned to the record.
    public function canEditIpcr(Ipcr $ipcr): bool
    {
        return (int) $ipcr->userid === (int) $this->id
            || $this->hasAdminAccessRight()
            || in_array((int) $this->id, array_map('intval', [$ipcr->supervisor_id, $ipcr->division_head, $ipcr->highest_supervisor]), true);
    }

    public function canViewSpcr(Spcr $spcr): bool
    {
        return (int) $spcr->userid === (int) $this->id
            || $this->hasAdminAccessRight()
            || $this->isPmt()
            || in_array((int) $this->id, array_map('intval', [$spcr->supervisor_id, $spcr->division_head_id, $spcr->highest_supervisor, $spcr->pmt_id]), true)
            || $this->managesUser($spcr->userid);
    }

    public function canEditSpcr(Spcr $spcr): bool
    {
        return (int) $spcr->userid === (int) $this->id
            || $this->hasAdminAccessRight()
            || in_array((int) $this->id, array_map('intval', [$spcr->supervisor_id, $spcr->division_head_id, $spcr->highest_supervisor]), true);
    }

    public function hasAdminAccessRight(): bool
    {
        try {
            $query = \Illuminate\Support\Facades\DB::connection('user')
                ->table('user_priv')
                ->where('user_id', $this->id)
                ->where('syscode', 'e-spms');

            $schema = \Illuminate\Support\Facades\DB::connection('user')->getSchemaBuilder();
            if ($schema->hasColumn('user_priv', 'access_rights')) {
                return (clone $query)
                    ->whereRaw('LOWER(TRIM(access_rights)) = ?', ['admin'])
                    ->exists();
            }

            // Legacy/user DB currently stores rights in `level` (e.g., ADMIN).
            if ($schema->hasColumn('user_priv', 'level')) {
                return (clone $query)
                    ->whereRaw('LOWER(TRIM(level)) = ?', ['admin'])
                    ->exists();
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isPmt()
    {
        try {
            return \Illuminate\Support\Facades\DB::connection('user')
                ->table('user_priv')
                ->where('user_id', $this->id)
                ->where('syscode', 'e-spms')
                ->where('level', 'PMT')
                ->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
