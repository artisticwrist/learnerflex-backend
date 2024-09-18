<?php

namespace App\Http\Controllers\PasswordReset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetSuccess;
use App\Models\User;

class NewPasswordReset extends Controller
{


public function resetPassword(Request $request)
{
    // Validate the request data
    $request->validate([
        'email' => 'required|email',
        'token' => 'required|string',
        'password' => 'required|string|min:8|confirmed',
    ]);


    // Find the user by email
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }




    // Check if the token is valid
    $tokenData = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->where('token', $request->token)
        ->first();

    if (!$tokenData) {
        return response()->json(['message' => 'Invalid or expired token'], 400);
    }



    // // Update the user's password
    $user->password = Hash::make($request->password);
    $user->save();

    // // Delete the reset token
    DB::table('password_reset_tokens')->where('email', $request->email)->delete();

    // Optionally, send a confirmation email
    Mail::to($user->email)->send(new \App\Mail\PasswordResetSuccess($user));

    return response()->json(['message' => 'Password reset successful'], 200);
}

}
