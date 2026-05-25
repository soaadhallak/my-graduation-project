<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\GithubProjectController;
use App\Http\Controllers\API\InvitationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Middleware\GuestOrAuthenticated;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function(){
    Route::post('/',[AuthController::class,'register']);
    Route::post('/login',[AuthController::class,'login']);
    Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/reset-password', [PasswordResetController::class, 'ResetPssword']);
});

Route::apiResource('/users', App\Http\Controllers\API\UserController::class);

Route::apiResource('projects', ProjectController::class)->middleware(['auth:sanctum']);
Route::post('github/repositories', [GithubProjectController::class, 'getRepositories'])->middleware('auth:sanctum');
Route::post('github/initialize-project', [GithubProjectController::class, 'initializeProject'])->middleware('auth:sanctum');

Route::post('invitations/{project}/invite', [InvitationController::class, 'inviteMember'])->middleware(['auth:sanctum']);
Route::post('invitations/accept', [InvitationController::class, 'acceptInvitation'])->middleware(GuestOrAuthenticated::class);