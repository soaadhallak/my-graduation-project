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
            $this->markFailed($project, 'Project or GitHub config not found.');

            return;
        }

        $this->markRunning($project);

        $config = $project->githubConfig;
        $token = $gitService->getInstallationToken($config->installation_id);

        if (! $token) {
            $this->markFailed($project, 'Failed to obtain GitHub installation token.');

            return;
        }

        $response = Http::withToken($token)
            ->timeout(120)
            ->get("https://github.com/{$config->full_name}/zipball/{$config->default_branch}");

        if (! $response->successful()) {
            $this->markFailed(
                $project,
                "GitHub zipball download failed (HTTP {$response->status()}) for {$config->full_name}@{$config->default_branch}."
            );

            return;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'repo_');
        file_put_contents($tempFile, $response->body());

        $zip = new ZipArchive;

        if ($zip->open($tempFile) !== true) {
            @unlink($tempFile);
            $this->markFailed($project, 'Could not open downloaded zip archive.');

            return;
        }

        try {
            [$fileIndex, $composerJson] = $this->buildFileIndex($zip);
            $psr4 = $resolver->parsePsr4FromComposerJson($composerJson);

            $inserted = DB::transaction(function () use ($zip, $fileIndex, $psr4, $extractor, $resolver) {
                Dependencie::where('project_id', $this->projectId)->delete();

                $rows = [];
                $total = 0;

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
                            $total += count($rows);
                            $rows = [];
                        }
                    }
                }

                if ($rows !== []) {
                    Dependencie::insert($rows);
                    $total += count($rows);
                }

                return $total;
            });

            $project->update([
                'analysis_status' => 'ready',
                'analysis_error' => null,
                'analysis_finished_at' => now(),
            ]);

            Log::info("Success: Analysis completed via Zipball for Project #{$this->projectId}", [
                'dependencies' => $inserted,
            ]);
        } catch (\Throwable $e) {
            $this->markFailed($project, $e->getMessage());

            throw $e;
        } finally {
            $zip->close();
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $project = Project::find($this->projectId);

        if ($project && $project->analysis_status !== 'ready') {
            $this->markFailed(
                $project,
                $exception?->getMessage() ?? 'Job failed after all retries.'
            );
        }
    }

    protected function markRunning(Project $project): void
    {
        $project->update([
            'analysis_status' => 'running',
            'analysis_error' => null,
            'analysis_started_at' => now(),
            'analysis_finished_at' => null,
        ]);
    }

    protected function markFailed(?Project $project, string $message): void
    {
        Log::error("Analysis failed for Project #{$this->projectId}: {$message}");

        if (! $project) {
            return;
        }

        $project->update([
            'analysis_status' => 'failed',
            'analysis_error' => $message,
            'analysis_finished_at' => now(),
        ]);
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
