<?php

namespace App\Http\Controllers\Api\Auth;

use App\Data\PasswordData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Actions\ForgotPasswordAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ResetPasswordRequest;
use App\Actions\ResetPasswordAction;

class PasswordResetController extends Controller
{
    public function sendResetLink(ForgotPasswordRequest $request, ForgotPasswordAction $action): JsonResponse
    {
        $message = $action->execute(PasswordData::from($request->validated()));

        return response()->json([
            'message' => $message
        ]);
    }

    public function ResetPssword(ResetPasswordRequest $request, ResetPasswordAction $resetPasswordAction): JsonResponse
    {
        $message = $resetPasswordAction->execute(PasswordData::from($request->validated()));

        return response()->json([
            'message' => $message
        ]);
    }
}
