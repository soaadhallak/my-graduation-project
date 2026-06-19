<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Label extends Model
{
    protected $fillable = [
        'name',
        'color',
    ];

    public function bugs(): BelongsToMany
    {
        return $this->belongsToMany(Bug::class, 'bug_label');
    }
}
