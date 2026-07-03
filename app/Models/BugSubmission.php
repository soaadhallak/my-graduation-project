<?php

namespace App\Models;

use App\Enums\BugSubmissionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BugSubmission extends Model
{
    protected $fillable = [
        'bug_id',
        'user_id',
        'commit_hash',
        'pull_request_number',
        'review_branch',
        'status',
        'rejection_reason'
    ];

    protected function casts(): array
    {
        return [
            'status' => BugSubmissionStatus::class
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

    public function changes(): HasMany
    {
        return $this->hasMany(BugSubmissionChange::class);
    }
}
