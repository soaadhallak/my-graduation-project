<?php

namespace App\Services\Dependency;

class DependencyPathResolver
{
    public function resolve(
        string $sourceFile,
        string $rawDependency,
        string $extension,
        array $fileIndex,
        array $psr4 = []
    ): ?string {
        return match ($extension) {
            'php' => $this->resolvePhp($rawDependency, $fileIndex, $psr4),
            'js', 'jsx', 'ts', 'tsx' => $this->resolveJs($sourceFile, $rawDependency, $fileIndex),
            'py' => $this->resolvePython($sourceFile, $rawDependency, $fileIndex),
            default => null,
        };
    }

    protected function resolvePhp(string $raw, array $fileIndex, array $psr4): ?string
    {
        // require/include style path
        if (str_contains($raw, '/') || str_ends_with(strtolower($raw), '.php')) {
            $candidate = ltrim(str_replace('\\', '/', $raw), '/');

            return $this->firstExisting($fileIndex, [
                $candidate,
                $candidate.'.php',
            ]);
        }

        $mappings = $psr4 !== [] ? $psr4 : ['App\\' => 'app/'];

        uksort($mappings, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($mappings as $namespace => $pathPrefix) {
            $ns = str_ends_with($namespace, '\\') ? $namespace : $namespace.'\\';

            if (! str_starts_with($raw, $ns)) {
                continue;
            }

            $relative = str_replace('\\', '/', substr($raw, strlen($ns)));
            $base = rtrim(str_replace('\\', '/', $pathPrefix), '/').'/'.$relative;

            return $this->firstExisting($fileIndex, [
                $base.'.php',
                $base,
            ]);
        }

        return null;
    }

    protected function resolveJs(string $sourceFile, string $raw, array $fileIndex): ?string
    {
        if (! str_starts_with($raw, '.') && ! str_starts_with($raw, '/')) {
            return null;
        }

        $dir = str_replace('\\', '/', dirname($sourceFile));
        if ($dir === '.') {
            $dir = '';
        }

        $joined = $this->normalizePath(($dir !== '' ? $dir.'/' : '').$raw);

        return $this->firstExisting($fileIndex, [
            $joined,
            $joined.'.js',
            $joined.'.jsx',
            $joined.'.ts',
            $joined.'.tsx',
            $joined.'/index.js',
            $joined.'/index.jsx',
            $joined.'/index.ts',
            $joined.'/index.tsx',
        ]);
    }

    protected function resolvePython(string $sourceFile, string $raw, array $fileIndex): ?string
    {
        if (str_starts_with($raw, '.')) {
            $dir = str_replace('\\', '/', dirname($sourceFile));
            if ($dir === '.') {
                $dir = '';
            }

            $dots = 0;
            while (isset($raw[$dots]) && $raw[$dots] === '.') {
                $dots++;
            }

            $rest = substr($raw, $dots);
            $baseDir = $dir;

            for ($i = 1; $i < $dots; $i++) {
                $baseDir = $baseDir !== '' ? dirname($baseDir) : '';
                if ($baseDir === '.') {
                    $baseDir = '';
                }
            }

            $relative = str_replace('.', '/', $rest);
            $joined = $this->normalizePath(($baseDir !== '' ? $baseDir.'/' : '').$relative);

            return $this->firstExisting($fileIndex, [
                $joined.'.py',
                $joined.'/__init__.py',
            ]);
        }

        $module = explode(',', $raw)[0];
        $module = trim(explode(' as ', $module)[0]);
        $relative = str_replace('.', '/', $module);

        return $this->firstExisting($fileIndex, [
            $relative.'.py',
            $relative.'/__init__.py',
        ]);
    }

    protected function firstExisting(array $fileIndex, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $normalized = $this->normalizePath($candidate);

            if ($normalized !== '' && isset($fileIndex[$normalized])) {
                return $normalized;
            }
        }

        return null;
    }

    public function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $parts = [];

        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                array_pop($parts);
                continue;
            }

            $parts[] = $part;
        }

        return implode('/', $parts);
    }

    public function parsePsr4FromComposerJson(?string $composerJson): array
    {
        if (! $composerJson) {
            return ['App\\' => 'app/'];
        }

        $data = json_decode($composerJson, true);

        if (! is_array($data)) {
            return ['App\\' => 'app/'];
        }

        $psr4 = [];

        foreach (['autoload', 'autoload-dev'] as $section) {
            foreach ($data[$section]['psr-4'] ?? [] as $namespace => $path) {
                if (is_array($path)) {
                    $path = $path[0] ?? '';
                }

                $psr4[$namespace] = rtrim(str_replace('\\', '/', (string) $path), '/').'/';
            }
        }

        return $psr4 !== [] ? $psr4 : ['App\\' => 'app/'];
    }
}
