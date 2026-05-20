<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectUser extends Model
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
