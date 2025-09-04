<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\UserFactory\AdminUserFactory;
use App\Services\UserFactory\RegularUserFactory;
use App\Services\UserFactory\GuestUserFactory;
use App\Models\User;

class UserController extends Controller
{
    public function __construct(
        private AdminUserFactory $adminFactory,
        private RegularUserFactory $userFactory,
        private GuestUserFactory $guestFactory
    ) {}

    /**
     * Crea un utente admin
     */
    public function createAdmin(Request $request): JsonResponse
    {
        try {
            $user = $this->adminFactory->createUser($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Admin user created successfully',
                'user' => $user->load('role')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Crea un utente normale
     */
    public function createUser(Request $request): JsonResponse
    {
        try {
            $user = $this->userFactory->createUser($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user->load('role')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Crea un utente guest
     */
    public function createGuest(Request $request): JsonResponse
    {
        try {
            $user = $this->guestFactory->createUser($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Guest user created successfully',
                'user' => $user->load('role')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Lista tutti gli utenti
     */
    public function index(): JsonResponse
    {
        $users = User::with('role')->get();
        
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Mostra un utente specifico
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => $user->load('role')
        ]);
    }

    /**
     * Verifica i permessi di un utente
     */
    public function checkPermissions(User $user, string $permission): JsonResponse
    {
        $hasPermission = $user->hasPermission($permission);
        
        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'permission' => $permission,
            'has_permission' => $hasPermission
        ]);
    }
}
