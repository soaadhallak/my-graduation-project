<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

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
            ->withPivot('role')
            ->withTimestamps();
    }

    public function bugs(): HasMany
    {
        return $this->hasMany(Bug::class);
    }
}
