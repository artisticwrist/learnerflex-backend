<?php

namespace App\Http\Controllers\Auths;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): JsonResponse
    {
        // Return the forgot password view name to the frontend as a JSON response.
        return response()->json([
            'view' => 'auth.forgot-password',
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Attempt to send the password reset link to the user
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Return a JSON response indicating success or failure
        if ($status == Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent successfully.',
                'status' => $status,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed to send password reset link.',
                'status' => $status,
                'errors' => ['email' => __($status)],
            ], 422);
        }
    }
}
 