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
}
