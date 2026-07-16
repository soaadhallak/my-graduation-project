<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LabelResource;
use App\Models\Label;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;

class LabelController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $labels = Label::query()->orderBy('name')->get();

        return LabelResource::collection($labels)
            ->additional([
                'message' => ResponseMessages::RETRIEVED->message(),
            ]);
    }
}
