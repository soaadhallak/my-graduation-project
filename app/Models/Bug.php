<?php

namespace App\Models;

use App\Enums\BugEnvironments;
use App\Enums\BugPriorities;
use App\Enums\BugStatuses;
use App\Traits\BugFilterQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mrmarchone\LaravelAutoCrud\Traits\HasMediaConversions;
use Spatie\MediaLibrary\HasMedia;

class Bug extends Model implements HasMedia
{
    use HasMediaConversions, BugFilterQuery;
    
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'environment',
        'project_id',
        'creator_id',
        'assigned_to',
    ];


    protected function casts(): array
    {
        return [
            'priority' => BugPriorities::class,
            'status' => BugStatuses::class,
            'environment' => BugEnvironments::class,
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(BugHistory::class)->orderBy('created_at', 'desc');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'bug_label');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(BugSubmission::class);
    }
}
