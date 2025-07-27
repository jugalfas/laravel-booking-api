<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();

        // Check if user is verified
        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Your email address is not verified.',
            ], 403);
        }

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Get role (assuming spatie/laravel-permission or similar)
        if (method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames();
            $role = $roles->isNotEmpty() ? $roles->first() : null;
        } else {
            $role = null;
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'stage_name' => $user->stage_name,
                'email' => $user->email,
                'role' => $role,
            ],
            'token' => $token,
        ]);
    }

    public function register(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'stage_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'type' => 'required|in:talent,client,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $userData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'stage_name' => $request->stage_name,
            'email' => $request->email,
            'password' => $request->password, // Will be hashed by model's mutator/cast
        ];

        // If admin, auto-verify email
        if ($request->type === 'admin') {
            $userData['email_verified_at'] = now();
        }

        $user = \App\Models\User::create($userData);

        // Assign role based on type, if spatie/laravel-permission is used
        if (method_exists($user, 'assignRole')) {
            $user->assignRole(Str::ucfirst($request->type));
            $role = $request->type;
        } else {
            $role = null;
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'stage_name' => $user->stage_name,
                'email' => $user->email,
                'role' => $role,
                'type' => $request->type,
            ],
        ], 201);
    }

    public function verifyEmail(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email is already verified.',
            ], 200);
        }

        $user->email_verified_at = now();
        $user->save();

        return response()->json([
            'message' => 'Email verified successfully.',
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => 'You are not logged in.',
            ], 401);
        }

        // Revoke all tokens for the user
        $user->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
