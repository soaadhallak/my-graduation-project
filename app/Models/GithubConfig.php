<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GithubConfig extends Model
{
    protected $fillable = [
        'project_id',
        'github_repo_id',
        'full_name',
        'installation_id',
        'default_branch',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
