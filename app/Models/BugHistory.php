<?php

namespace App\Models;

use App\Enums\BugHistoryTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;

class BugHistory extends Model
{
    protected $fillable = [
        'bug_id',
        'user_id',
        'type',
        'from_state',
        'to_state',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => BugHistoryTypes::class,
        ];
    }

    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
