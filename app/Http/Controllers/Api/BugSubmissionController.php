<?php

namespace App\Http\Controllers\Api;

use App\Data\BugSubmissionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmitBugRequest;
use App\Http\Resources\BugSubmissionResource;
use App\Models\BugSubmission;
use App\Services\BugSubmissionsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;

class BugSubmissionController extends Controller
{
    public function __construct(
        protected BugSubmissionsService $submissionService
    ) {}


    public function submit(StoreSubmitBugRequest $request)
    {
        try {
            $submission = $this->submissionService->store(
                BugSubmissionData::from($request->validated()),
                Auth::user()
            );

            return BugSubmissionResource::make($submission->load(['bug', 'user', 'changes']))
                ->additional([
                    'message' => ResponseMessages::CREATED->message()
                ])
                ->response()
                ->setStatusCode(201);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the submission'
            ], 500);
        }
    }

    public function show(BugSubmission $submission): BugSubmissionResource
    {
        Gate::authorize('view', $submission);
        $submission->load(['bug', 'user', 'changes']);

        return new BugSubmissionResource($submission)
            ->additional([
                'message' => ResponseMessages::RETRIEVED->message()
            ]);
    }
}   
