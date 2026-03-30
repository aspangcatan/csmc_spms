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

    public function isSupervisor()
    {
        return \Illuminate\Support\Facades\DB::connection('user')->table('section')->where('head', $this->id)->exists() ||
               \Illuminate\Support\Facades\DB::connection('user')->table('division')->where('head', $this->id)->exists();
    }

    public function isSectionHead()
    {
        return \Illuminate\Support\Facades\DB::connection('user')->table('section')->where('head', $this->id)->exists();
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
        return $this->hasAdminAccessRight() || $this->isSupervisor() || $this->isSectionHead();
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
