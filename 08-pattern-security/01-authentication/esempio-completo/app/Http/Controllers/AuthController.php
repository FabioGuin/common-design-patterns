<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        
        if ($this->authService->attemptLogin($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => Auth::user()
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    /**
     * Show registration form
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $userData = $request->validated();
        
        $user = $this->authService->registerUser($userData);
        
        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'user' => $user
        ], 201);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request);
        
        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Get current user
     */
    public function me(): JsonResponse
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(): JsonResponse
    {
        $token = $this->authService->refreshToken();
        
        return response()->json([
            'success' => true,
            'token' => $token
        ]);
    }
}
