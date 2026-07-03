<?php

namespace App\Http\Controllers\Api;

use App\Data\BugSubmissionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveBugSubmissionRequest;
use App\Http\Requests\RejectBugSubmissionRequest;
use App\Http\Requests\StoreSubmitBugRequest;
use App\Http\Resources\BugSubmissionResource;
use App\Models\BugSubmission;
use App\Services\BugSubmissionsService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
        $submission->load(['bug.creator', 'user', 'changes']);

        return BugSubmissionResource::make($submission)
            ->additional([
                'message' => ResponseMessages::RETRIEVED->message()
            ]);
    }

    public function approve(BugSubmission $submission, ApproveBugSubmissionRequest $request)
    {
        try {
            $this->submissionService->approve($submission, Auth::user());

            return BugSubmissionResource::make($submission->load(['bug', 'user', 'changes']))
                ->additional([
                    'message' => ResponseMessages::UPDATED->message()
                ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while approving the submission'
            ], 500);
        }
    }

   public function reject(BugSubmission $submission, RejectBugSubmissionRequest $request)
    {
        try {
            $submission = $this->submissionService->reject($submission, $request->validated()['reason'], auth()->user());

            return BugSubmissionResource::make($submission)
                ->additional([
                    'message' => ResponseMessages::UPDATED->message()
                ]);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while rejecting the submission'
            ], 500);
        }
    }
}   
