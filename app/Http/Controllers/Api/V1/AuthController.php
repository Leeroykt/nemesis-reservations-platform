<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;

class AuthController extends Controller
{
    /**
     * Login user and return token + user data.
     * POST /api/v1/login
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        /** @var NewAccessToken $token */
        $token = $user->createToken('auth_token');

        return response()->json([
            'data' => [
                'user' => $user,
                'token' => $token->plainTextToken,
            ],
        ]);
    }

    /**
     * Logout user (revoke token).
     * POST /api/v1/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'data' => null,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Get authenticated user.
     * GET /api/v1/me
     */
    public function me(Request $request)
    {
        return response()->json([
            'data' => $request->user(),
        ]);
    }
}
