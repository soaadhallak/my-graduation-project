<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'role',
        'status',
        'project_id',
        'token',
        'expires_at',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
