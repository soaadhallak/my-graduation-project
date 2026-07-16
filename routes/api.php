<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\GithubProjectController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Middleware\GuestOrAuthenticated;
use App\Http\Controllers\Api\BugController;
use App\Http\Controllers\Api\BugSubmissionController;
use App\Http\Controllers\Api\BugUserController;

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

Route::apiResource('projects', ProjectController::class)->middleware(['auth:sanctum']);
Route::get('projects/{project}/members', [ProjectController::class, 'members'])->middleware(['auth:sanctum']);
Route::get('projects/{project}/github-config', [ProjectController::class, 'githubConfig'])->middleware(['auth:sanctum']);
Route::post('github/repositories', [GithubProjectController::class, 'getRepositories'])->middleware('auth:sanctum');
Route::post('github/initialize-project', [GithubProjectController::class, 'initializeProject'])->middleware('auth:sanctum');
Route::get('github/install-link', [GithubProjectController::class, 'getInstallLink'])->middleware('auth:sanctum');

Route::post('invitations/{project}/invite', [InvitationController::class, 'inviteMember'])->middleware(['auth:sanctum']);
Route::post('invitations/accept', [InvitationController::class, 'acceptInvitation'])->middleware(GuestOrAuthenticated::class);

Route::apiResource('bugs', BugController::class)->middleware(['auth:sanctum'])->except(['index']);
Route::post('/bugs/{bug}/test-pass', [BugController::class, 'passBug'])->middleware(['auth:sanctum']);
Route::post('/bugs/{bug}/test-fail', [BugController::class, 'failBug'])->middleware(['auth:sanctum']);

Route::get('projects/{project}/bugs', [BugController::class, 'index'])->middleware(['auth:sanctum']);

Route::apiResource('my-bugs', BugUserController::class)->middleware(['auth:sanctum']);

// TODO: راوت تشخيص مؤقت - احذفيه بعد حل المشكلة
Route::get('/debug/github-key', function () {
    $envPath = env('GITHUB_PRIVATE_KEY_PATH');
    $resolvedPath = base_path($envPath ?? '');
    $keysDir = base_path('storage/app/keys');

    return response()->json([
        'config_is_cached' => app()->configurationIsCached(),
        'env_GITHUB_PRIVATE_KEY_PATH' => $envPath,
        'env_GITHUB_APP_ID' => env('GITHUB_APP_ID'),
        'resolved_path' => $resolvedPath,
        'file_exists' => is_file($resolvedPath),
        'is_readable' => is_readable($resolvedPath),
        'keys_dir_exists' => is_dir($keysDir),
        'keys_dir_contents' => is_dir($keysDir) ? array_values(array_diff(scandir($keysDir), ['.', '..'])) : null,
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/submissions', [BugSubmissionController::class, 'submit']);
    Route::get('/submissions/{submission}', [BugSubmissionController::class, 'show']);
    Route::post('/submissions/{submission}/approve', [BugSubmissionController::class, 'approve']);
    Route::post('/submissions/{submission}/reject', [BugSubmissionController::class, 'reject']);
});