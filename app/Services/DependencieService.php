<?php

namespace App\Services;

use App\Data\DependencieData;
use App\Models\Dependencie;
use Illuminate\Support\Facades\DB;

class DependencieService
{
    public function store(DependencieData $data): Dependencie
    {
        return DB::transaction(function () use ($data) {
            $dependencie = Dependencie::create($data->onlyModelAttributes());

            return $dependencie;
        });
    }

    public function update(DependencieData $data,  Dependencie $dependencie): Dependencie
    {
        return DB::transaction(function () use ($data, $dependencie) {
            tap($dependencie)->update($data->onlyModelAttributes());

            return $dependencie;
        });
    }
}
