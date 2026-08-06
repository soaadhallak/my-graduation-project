<?php

namespace App\Traits;

use App\Models\BugSubmission;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

trait BugSubmissionFilterQuery
{
    public static function getQuery(): QueryBuilder
    {
        return QueryBuilder::for(BugSubmission::class)
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::exact('bugId', 'bug_id'),
                AllowedFilter::exact('submittedBy', 'user_id'),
                AllowedFilter::callback('projectId', function ($query, $value) {
                    $query->whereHas('bug', fn ($q) => $q->where('project_id', $value));
                }),
            )
            ->allowedSorts('created_at', 'updated_at', 'status')
            ->defaultSort('-created_at');
    }
}
