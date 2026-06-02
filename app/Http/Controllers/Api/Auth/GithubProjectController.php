<?php

namespace App\Http\Controllers\Api\Auth;

use App\Data\GithubConfigData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGithubConfigRequest;
use App\Http\Resources\GithubConfigResource;
use App\Jobs\AnalyzeProjectDependencies;
use App\Services\GithubConfigService;
use Illuminate\Http\Request;
use App\Services\SocialAuthService;
use Illuminate\Support\Facades\Http;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class GithubProjectController extends Controller
{
    public function __construct(
        protected SocialAuthService $gitService,
        protected GithubConfigService $githubConfigService,
    ) {}

    public function getRepositories(Request $request)
    {
        try {
            $installationId = $request->installationId;

            $token = $this->gitService->getInstallationToken($installationId);

            if (!$token) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $response = Http::withToken($token)
                ->get('https://api.github.com/installation/repositories');

            return response()->json([
                "data" => [
                    'repositories' => $response->json()['repositories'] ?? [],
                    'installation_id' => $installationId,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch repositories', 'message' => $e->getMessage()], 500);
        }
    }

    public function initializeProject(StoreGithubConfigRequest $request): JsonResponse
    {
        try {
            $githubConfig = $this->githubConfigService->store(GithubConfigData::from($request->validated()));
            AnalyzeProjectDependencies::dispatch($request->projectId);

            return GithubConfigResource::make($githubConfig->load(['project']))
                ->additional([
                    'message' => ResponseMessages::CREATED->message()
                ])
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to initialize project', 'message' => $e->getMessage()], 500);
        }
    }

    public function getInstallLink(): JsonResponse
    {
        $url = config('services.github.app_url')? config('services.github.app_url') : 'https://github.com/apps/bugflowapp';

        if (!$url) {
            return response()->json([
                'error' => 'GitHub App URL is not configured'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'url' => $url  
        ]);
    }
}
