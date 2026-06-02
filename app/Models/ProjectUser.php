<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectUser extends Pivot
{
    protected $table = 'project_users';

    public $incrementing = true;

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo {
        return $this->belongsTo(Project::class);
    }

}
