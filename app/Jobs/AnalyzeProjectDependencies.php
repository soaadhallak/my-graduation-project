<?php

namespace App\Jobs;

use App\Models\Dependencie;
use App\Models\Project;
use App\Services\Dependency\DependencyPathResolver;
use App\Services\Dependency\ImportExtractor;
use App\Services\SocialAuthService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class AnalyzeProjectDependencies implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 600;

    public function __construct(protected int $projectId) {}

    public function handle(
        SocialAuthService $gitService,
        ImportExtractor $extractor,
        DependencyPathResolver $resolver
    ): void {
        $project = Project::with('githubConfig')->find($this->projectId);

        if (! $project || ! $project->githubConfig) {
            return;
        }

        $config = $project->githubConfig;
        $token = $gitService->getInstallationToken($config->installation_id);

        if (! $token) {
            Log::error("Failed to obtain GitHub token for Project #{$this->projectId}");

            return;
        }

        $response = Http::withToken($token)
            ->timeout(120)
            ->get("https://github.com/{$config->full_name}/zipball/{$config->default_branch}");

        if (! $response->successful()) {
            Log::error('GitHub Zipball API failed: '.$response->status());

            return;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'repo_');
        file_put_contents($tempFile, $response->body());

        $zip = new ZipArchive;

        if ($zip->open($tempFile) !== true) {
            Log::error("Could not open Zip file for Project #{$this->projectId}");
            @unlink($tempFile);

            return;
        }

        try {
            [$fileIndex, $composerJson] = $this->buildFileIndex($zip);
            $psr4 = $resolver->parsePsr4FromComposerJson($composerJson);

            DB::transaction(function () use ($zip, $fileIndex, $psr4, $extractor, $resolver, $project) {
                Dependencie::where('project_id', $this->projectId)->delete();

                $rows = [];

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryPath = $zip->getNameIndex($i);

                    if (! preg_match('/\.(php|py|js|jsx|ts|tsx)$/i', $entryPath)) {
                        continue;
                    }

                    $cleanPath = $this->stripRootFolder($entryPath);
                    $extension = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
                    $content = $zip->getFromIndex($i);

                    if ($content === false) {
                        continue;
                    }

                    foreach ($extractor->extract($content, $extension) as $rawDependency) {
                        if ($rawDependency === '' || strlen($rawDependency) > 250) {
                            continue;
                        }

                        $resolved = $resolver->resolve(
                            $cleanPath,
                            $rawDependency,
                            $extension,
                            $fileIndex,
                            $psr4
                        );

                        $rows[] = [
                            'project_id' => $this->projectId,
                            'file_path' => $cleanPath,
                            'depends_on' => $rawDependency,
                            'depends_on_path' => $resolved,
                            'extension' => $extension,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        if (count($rows) >= 500) {
                            Dependencie::insert($rows);
                            $rows = [];
                        }
                    }
                }

                if ($rows !== []) {
                    Dependencie::insert($rows);
                }

                $project->update(['status' => 'ready']);
            });

            Log::info("Success: Analysis completed via Zipball for Project #{$this->projectId}");
        } catch (\Throwable $e) {
            Log::error('Analysis Error: '.$e->getMessage(), [
                'project_id' => $this->projectId,
            ]);
        } finally {
            $zip->close();
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * @return array{0: array<string, true>, 1: ?string}
     */
    protected function buildFileIndex(ZipArchive $zip): array
    {
        $fileIndex = [];
        $composerJson = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryPath = $zip->getNameIndex($i);

            if (str_ends_with($entryPath, '/')) {
                continue;
            }

            $cleanPath = $this->stripRootFolder($entryPath);

            if ($cleanPath === '') {
                continue;
            }

            $fileIndex[$cleanPath] = true;

            if ($cleanPath === 'composer.json') {
                $composerJson = $zip->getFromIndex($i) ?: null;
            }
        }

        return [$fileIndex, $composerJson];
    }

    protected function stripRootFolder(string $entryPath): string
    {
        $parts = explode('/', str_replace('\\', '/', $entryPath));

        // GitHub zipball prefixes with "owner-repo-sha/"
        array_shift($parts);

        return implode('/', array_filter($parts, fn ($part) => $part !== ''));
    }
}
