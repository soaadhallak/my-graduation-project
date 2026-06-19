<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BugSubmissionChange extends Model
{
    protected $fillable = [
        'bug_submission_id',
        'file',
        'diff',
    ];

    public function bugSubmission(): BelongsTo
    {
        return $this->belongsTo(BugSubmission::class, 'bug_submission_id');
    }
}
