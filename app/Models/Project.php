<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'analysis_status',
        'analysis_error',
        'analysis_started_at',
        'analysis_finished_at',
    ];

    protected function casts(): array
    {
        return [
            'analysis_started_at' => 'datetime',
            'analysis_finished_at' => 'datetime',
        ];
    }

    public function githubConfig(): HasOne
    {
        return $this->hasOne(GithubConfig::class);
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(Dependencie::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')
            ->using(ProjectUser::class)
            ->withPivot('id', 'role')
            ->withTimestamps();
    }

    public function projectUsers(): HasMany
    {
        return $this->hasMany(ProjectUser::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function bugs(): HasMany
    {
        return $this->hasMany(Bug::class);
    }

    public function projectManager(): ?User
    {
        return $this->members()
            ->wherePivot('role', UserRole::PROJECT_MANAGER->value)
            ->first();
    }
}
