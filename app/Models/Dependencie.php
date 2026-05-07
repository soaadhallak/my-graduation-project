<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dependencie extends Model
{
    protected $fillable = [
        'project_id',
        'file_path',
        'depends_on',
        'extension',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
