<?php

namespace App\Traits;

use App\Models\Bug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Mrmarchone\LaravelAutoCrud\Helpers\SearchTermEscaper;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


trait BugFilterQuery
{
    public static function getQuery(): QueryBuilder
    {
        return QueryBuilder::for(Bug::class)
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::exact('priority'),
                AllowedFilter::exact('assignedTo', 'assigned_to'),
                AllowedFilter::exact('creatorId', 'creator_id'),
                AllowedFilter::exact('environment'),
                AllowedFilter::exact('projectId', 'project_id'),
                AllowedFilter::scope('search'),
            )
            ->allowedSorts('created_at', 'priority', 'title', 'updated_at')
            ->defaultSort('-created_at');
    }


    public function scopeSearch(Builder $query, string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $likeTerm = SearchTermEscaper::escape($term);

        return $query->where(function (Builder $q) use ($likeTerm) {
            $q->whereRaw("title LIKE ? ESCAPE '!'", [$likeTerm])
                ->orWhereRaw("description LIKE ? ESCAPE '!'", [$likeTerm]);
        });
    }
}
