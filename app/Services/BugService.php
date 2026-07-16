<?php

namespace App\Services;

use App\Data\BugData;
use App\Enums\BugHistoryTypes;
use App\Enums\BugStatuses;
use App\Models\Bug;
use App\Models\BugHistory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Mrmarchone\LaravelAutoCrud\Helpers\MediaHelper;

class BugService
{
    public function store(BugData $data, User $user): Bug
    {
        return DB::transaction(function () use ($data, $user) {
            $bug = Bug::create([
                'title' => $data->title,
                'description' => $data->description,
                'status' => $data->status ?? BugStatuses::BACKLOG->value,
                'priority' => $data->priority,
                'environment' => $data->environment,
                'project_id' => $data->projectId,
                'creator_id' => $user->id,
                'assigned_to' => $data->assignedTo ?? null,
            ]);

            if ($data->screenshot instanceof UploadedFile) {
                MediaHelper::uploadMedia($data->screenshot, $bug, 'screenshot');
            }


            if(!empty($data->labels)) {
                $bug->labels()->sync($data->labels);
            }

            BugHistory::create([
                'bug_id' => $bug->id,
                'user_id' => $user->id,
                'type' => BugHistoryTypes::STATUS_CHANGE->value,
                'from_state' => null,
                'to_state' => $bug->status,
            ]);

            if($data->assignedTo) {
                BugHistory::create([
                    'bug_id' => $bug->id,
                    'user_id' => $user->id,
                    'type' => BugHistoryTypes::ASSIGNMENT_CHANGE->value,
                    'from_state' => null,
                    'to_state' => $bug->assigned_to ,
                ]);
            }

            return $bug;
        });
    }

    public function update(Bug $bug, BugData $data, User $user): Bug
    {
        return DB::transaction(function () use ($bug, $data, $user) {
            $originalStatus = $bug->status;
            $originalAssignee = $bug->assigned_to;

            tap($bug)->update($data->onlyModelAttributes());

            if ($data->screenshot instanceof UploadedFile) {
                MediaHelper::updateMedia($data->screenshot, $bug, 'screenshot');
            }

            if(!empty($data->labels)) {
                $bug->labels()->sync($data->labels);
            }

            if ($originalStatus != $bug->status) {
                BugHistory::create([
                    'bug_id' => $bug->id,
                    'user_id' => $user->id,
                    'type' => BugHistoryTypes::STATUS_CHANGE->value,
                    'from_state' => $originalStatus,
                    'to_state' => $bug->status,
                ]);
            }

            if ($originalAssignee != $bug->assigned_to) {
                BugHistory::create([
                    'bug_id' => $bug->id,
                    'user_id' => $user->id,
                    'type' => BugHistoryTypes::ASSIGNMENT_CHANGE->value,
                    'from_state' => $originalAssignee,
                    'to_state' => $bug->assigned_to ,
                ]);
            }

            return $bug;
        });
    }

}
