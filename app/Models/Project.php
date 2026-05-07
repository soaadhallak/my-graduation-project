<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
}
