<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Attempt to login user
     */
    public function attemptLogin(array $credentials, bool $remember = false): bool
    {
        $attempt = Auth::attempt($credentials, $remember);
        
        if ($attempt) {
            Log::info('User login successful', [
                'user_id' => Auth::id(),
                'email' => $credentials['email'],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        } else {
            Log::warning('User login failed', [
                'email' => $credentials['email'],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }
        
        return $attempt;
    }

    /**
     * Register new user
     */
    public function registerUser(array $userData): User
    {
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'email_verification_token' => Str::random(60),
            'is_active' => true
        ]);
        
        Log::info('User registered successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip()
        ]);
        
        return $user;
    }

    /**
     * Logout user
     */
    public function logout(Request $request): void
    {
        $userId = Auth::id();
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        Log::info('User logout', [
            'user_id' => $userId,
            'ip' => $request->ip()
        ]);
    }

    /**
     * Refresh authentication token
     */
    public function refreshToken(): string
    {
        $user = Auth::user();
        $user->tokens()->delete();
        
        $token = $user->createToken('auth-token')->plainTextToken;
        
        Log::info('Token refreshed', [
            'user_id' => $user->id
        ]);
        
        return $token;
    }

    /**
     * Verify email
     */
    public function verifyEmail(string $token): bool
    {
        $user = User::where('email_verification_token', $token)->first();
        
        if (!$user) {
            return false;
        }
        
        $user->update([
            'email_verified_at' => now(),
            'email_verification_token' => null
        ]);
        
        Log::info('Email verified', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
        
        return true;
    }

    /**
     * Send password reset
     */
    public function sendPasswordReset(string $email): bool
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return false;
        }
        
        $token = Str::random(60);
        $user->update(['password_reset_token' => $token]);
        
        // In a real application, send email here
        Log::info('Password reset token generated', [
            'user_id' => $user->id,
            'email' => $user->email,
            'token' => $token
        ]);
        
        return true;
    }

    /**
     * Reset password
     */
    public function resetPassword(string $token, string $password): bool
    {
        $user = User::where('password_reset_token', $token)->first();
        
        if (!$user) {
            return false;
        }
        
        $user->update([
            'password' => Hash::make($password),
            'password_reset_token' => null
        ]);
        
        Log::info('Password reset successful', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
        
        return true;
    }

    /**
     * Get authentication statistics
     */
    public function getAuthStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'recent_logins' => User::where('last_login_at', '>=', now()->subDays(7))->count(),
            'current_user' => Auth::user() ? [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'last_login' => Auth::user()->last_login_at
            ] : null
        ];
    }
}
