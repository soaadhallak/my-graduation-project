<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BugFilterRequest;
use App\Http\Resources\BugResource;
use Illuminate\Http\Request;
use App\Models\Bug;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;

class BugUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BugFilterRequest $request): AnonymousResourceCollection
    {
        $bugs = Bug::getQuery()
            ->where(function ($query) {
                $query->where('creator_id', Auth::id())
                    ->orWhere('assigned_to', Auth::id());
            })
            ->with(['project', 'creator', 'labels', 'assignedUser'])
            ->paginate($request->input('perPage', 15))
            ->withQueryString();

        return BugResource::collection($bugs)
            ->additional([
                'message' => ResponseMessages::RETRIEVED->message()
            ]);    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
