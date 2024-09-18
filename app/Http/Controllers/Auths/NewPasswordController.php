<?php

namespace App\Http\Controllers\Auths;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): JsonResponse
    {
        // Return the reset password view name to the frontend as a JSON response.
        return response()->json([
            'view' => 'auth.reset-password',
            'request' => $request->only('token', 'email'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the incoming request
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Attempt to reset the user's password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Return a JSON response indicating success or failure
        if ($status == Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password has been reset successfully.',
                'status' => $status,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed to reset password.',
                'status' => $status,
                'errors' => ['email' => __($status)],
            ], 422);
        }
    }
}
