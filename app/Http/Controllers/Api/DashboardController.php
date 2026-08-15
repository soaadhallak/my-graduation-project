<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardStatsResource;
use App\Services\DashboardStatsService;
use Illuminate\Support\Facades\Auth;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;

class DashboardController extends Controller
{
    public function __construct(private DashboardStatsService $dashboardStatsService) {}

    public function stats(): DashboardStatsResource
    {
        $stats = $this->dashboardStatsService->getStats(Auth::user());

        return DashboardStatsResource::make($stats)
            ->additional([
                'message' => ResponseMessages::RETRIEVED->message(),
            ]);
    }
}
