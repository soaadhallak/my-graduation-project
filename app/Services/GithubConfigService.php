<?php

namespace App\Services;

use App\Data\GithubConfigData;
use App\Models\GithubConfig;
use Illuminate\Support\Facades\DB;

class GithubConfigService
{
    public function store(GithubConfigData $data): GithubConfig
    {
        return DB::transaction(function () use ($data) {
            $githubConfig = GithubConfig::create($data->onlyModelAttributes());

            return $githubConfig;
        });
    }

    public function update(GithubConfigData $data,  GithubConfig $githubConfig): GithubConfig
    {
        return DB::transaction(function () use ($data, $githubConfig) {
            tap($githubConfig)->update($data->onlyModelAttributes());

            return $githubConfig;
        });
    }
}
