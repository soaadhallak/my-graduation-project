<?php

namespace App\Services\Dependency;

class ImportExtractor
{
    public function extract(string $content, string $extension): array
    {
        $patterns = match ($extension) {
            'php' => [
                '/\buse\s+([a-zA-Z_\\\\][a-zA-Z0-9_\\\\]*)\s*;/',
                '/\b(?:require|include)(?:_once)?\s*\(?\s*[\'"]([^\'"]+)[\'"]/',
            ],
            'js', 'jsx', 'ts', 'tsx' => [
                '/\bimport\s+(?:[^\'"]+\s+from\s+)?[\'"]([^\'"]+)[\'"]/',
                '/\bimport\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
                '/\brequire\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
                '/\bexport\s+(?:.+\s+)?from\s+[\'"]([^\'"]+)[\'"]/',
            ],
            'py' => [
                '/^\s*from\s+([a-zA-Z0-9_.]+)\s+import\s+/m',
                '/^\s*import\s+([a-zA-Z0-9_.]+)/m',
            ],
            default => [],
        };

        if ($patterns === []) {
            return [];
        }

        $results = [];

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $matches);

            foreach ($matches[1] ?? [] as $match) {
                $match = trim($match);

                if ($match !== '') {
                    $results[] = $match;
                }
            }
        }

        return array_values(array_unique($results));
    }
}
