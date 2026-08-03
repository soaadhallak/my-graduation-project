<?php

namespace App\Services\Dependency;

use App\Models\Dependencie;

class DependencyMapBuilder
{
    public function build(int $projectId): array
    {
        $edges = Dependencie::query()
            ->where('project_id', $projectId)
            ->whereNotNull('depends_on_path')
            ->get(['file_path', 'depends_on_path']);

        return $this->buildFromEdges($projectId, $edges);
    }

    /**
     * @param  iterable<object{file_path: string, depends_on_path: string}>  $edges
     * @return array<string, mixed>
     */
    public function buildFromEdges(int $projectId, iterable $edges): array
    {
        /** @var array<string, array<string, true>> $reverse */
        $reverse = [];

        foreach ($edges as $edge) {
            $target = $this->normalize($edge->depends_on_path);
            $source = $this->normalize($edge->file_path);

            if ($target === '' || $source === '' || $target === $source) {
                continue;
            }

            $reverse[$target][$source] = true;
        }

        $dependencies = [];

        foreach ($reverse as $file => $callers) {
            $callerList = array_keys($callers);
            sort($callerList);

            $dependencies[$file] = [
                'is_called_by' => $callerList,
            ];
        }

        ksort($dependencies);

        return [
            'version' => '1',
            'generated_from' => 'server',
            'project_id' => $projectId,
            'generated_at' => now()->toIso8601String(),
            'dependencies' => $dependencies,
        ];
    }

    protected function normalize(string $path): string
    {
        return str_replace('\\', '/', ltrim($path, '/'));
    }
}
