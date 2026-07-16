<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Dependencie;
use App\Services\DependencieService;
use App\Services\SocialAuthService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class AnalyzeProjectDependencies implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; 
    public $timeout = 600; 

    public function __construct(protected int $projectId) {}

    public function handle(SocialAuthService $gitService)
    {
        $project = Project::with('githubConfig')->find($this->projectId);
        if (!$project || !$project->githubConfig) return;

        $config = $project->githubConfig;
        $token = $gitService->getInstallationToken($config->installation_id);

        if (!$token) {
            Log::error("Failed to obtain GitHub token for Project #{$this->projectId}");
            return;
        }

        $response = Http::withToken($token)
            ->timeout(120) 
            ->get("https://github.com/{$config->full_name}/zipball/{$config->default_branch}");

        if (!$response->successful()) {
            Log::error("GitHub Zipball API failed: " . $response->status());
            return;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'repo_');
        file_put_contents($tempFile, $response->body());

        $zip = new \ZipArchive;
        if ($zip->open($tempFile) == TRUE) {
            DB::beginTransaction();
            try {
                Dependencie::where('project_id', $this->projectId)->delete();

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filePath = $zip->getNameIndex($i);
                    
                    if (preg_match('/\.(php|py|js)$/', $filePath)) {
                        $content = $zip->getFromIndex($i);
                        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                        
                        $dependencies = $this->extractImports($content, $extension);

                        foreach ($dependencies as $dep) {
                            if (strlen($dep) > 250 || empty($dep)) continue;
                            $cleanPath = implode('/', array_slice(explode('/', $filePath), 1));

                            Dependencie::create([
                                'project_id' => $this->projectId,
                                'file_path'  => $cleanPath?: $filePath, 
                                'depends_on' => $dep,
                                'extension'  => $extension,
                            ]);

                        }
                    }
                }

                $project->update(['status' => 'ready']);
                DB::commit();
                Log::info("Success: Analysis completed via Zipball for Project #{$this->projectId}");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Analysis Loop Error: " . $e->getMessage());
            } finally {
                $zip->close();
                if (file_exists($tempFile)) unlink($tempFile);
            }
        } else {
            Log::error("Could not open Zip file for Project #{$this->projectId}");
        }
    }

    private function extractImports($content, $extension)
    {
        $patterns = [
            'php' => '/use\s+([a-zA-Z0-9_\\\\]+)/',
            'js'  => '/import\s+.*\s+from\s+[\'"](.+)[\'"]/',
            'py'  => '/^\s*(?:from\s+([a-zA-Z0-9_.]+)\s+import|import\s+([a-zA-Z0-9_.]+))/m',
        ];

        if (!isset($patterns[$extension])) return [];

        preg_match_all($patterns[$extension], $content, $matches);

        $results = [];

        foreach ($matches as $index => $group) {
            if ($index == 0) continue; 
            foreach ($group as $match) {
                if (!empty($match)) $results[] = trim($match);
            }
        }

        return array_unique($results);
    }
}

