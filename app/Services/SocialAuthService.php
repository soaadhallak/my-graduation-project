<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialAuthService
{
    protected $appId;
    protected $privateKeyPath;

    public function __construct()
    {
        $this->appId = env('GITHUB_APP_ID');
        $this->privateKeyPath = base_path(env('GITHUB_PRIVATE_KEY_PATH'));
    }

    private function generateJwt()
    {
        $configuration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file($this->privateKeyPath),
            InMemory::file($this->privateKeyPath)
        );

        $issuedAt = new \DateTimeImmutable('@' . time());
        $expiresAt = $issuedAt->add(new \DateInterval('PT10M'));

        $token = $configuration->builder()
            ->issuedBy((string) $this->appId)
            ->issuedAt($issuedAt)
            ->expiresAt($expiresAt)
            ->getToken($configuration->signer(), $configuration->signingKey());

        Log::info('Generated JWT for GitHub App', [
            'app_id' => $this->appId,
            'issued_at' => $issuedAt->format('Y-m-d H:i:s'),
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'token' => $token->toString(),
        ]);

        return $token->toString();
    }

    public function getInstallationToken($installationId)
    {
        $jwt = $this->generateJwt();

        $url = "https://api.github.com/app/installations/{$installationId}/access_tokens";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $jwt,
            'Accept' => 'application/vnd.github+json',
        ])->post($url);

        Log::info('GitHub API Response', ['status' => $response->status(), 'body' => $response->body()]);

        if ($response->failed()) {
            Log::error('Failed to obtain installation token from GitHub', [
                'installation_id' => $installationId,
                'status' => $response->status(),
                'response_body' => $response->body(),
                'error_message' => $response->json()['message'] ?? 'No message provided',
            ]);
            return null;
        }
        return $response->json()['token'] ?? null;
    }

    public function mergePullRequest(int $installationId, string $owner, string $repo, int $prNumber, string $commitTitle): void
    {
        $token = $this->getInstallationToken($installationId);

        $url = "https://api.github.com/repos/{$owner}/{$repo}/pulls/{$prNumber}/merge";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2026-03-10',
        ])->put($url, [
            'commit_title' => $commitTitle,
            'merge_method' => 'squash',
        ]);

        if ($response->failed()) {
            Log::error('GitHub Squash & Merge Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Failed to merge Pull Request on GitHub.');
        }
    }

    public function createPullRequest($installationId, $owner, $repo, $headBranch, $baseBranch, $title)
    {
        $token = $this->getInstallationToken($installationId);

        if (!$token) {
            throw new \Exception('Could not obtain GitHub installation token.');
        }

        $url = "https://api.github.com/repos/{$owner}/{$repo}/pulls";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2026-03-10',
        ])->post($url, [
            'title' => $title,
            'head'  => $headBranch,
            'base'  => $baseBranch,
            'body'  => 'Automated PR created via BugFlow CLI',
        ]);

        if ($response->failed()) {
            Log::error('GitHub Create PR Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Failed to create Pull Request on GitHub.');
        }

        return $response->json()['number'];
    }

    public function deleteBranch(int $installationId, string $owner, string $repo, string $branchName): void
    {
        $token = $this->getInstallationToken($installationId);

        $url = "https://api.github.com/repos/{$owner}/{$repo}/git/refs/heads/{$branchName}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2026-03-10',
        ])->delete($url);

        if ($response->failed()) {
            Log::warning("GitHub Delete Branch Failed for {$branchName}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
    }

    public function closePullRequest(int $installationId, string $owner, string $repo, int $prNumber): void
    {
        $token = $this->getInstallationToken($installationId);
        $url = "https://api.github.com/repos/{$owner}/{$repo}/pulls/{$prNumber}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2026-03-10',
        ])->patch($url, [
            'state' => 'closed'
        ]);

        if ($response->failed()) {
            Log::error('GitHub Close PR Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Failed to close Pull Request on GitHub.');
        }
    }
}
