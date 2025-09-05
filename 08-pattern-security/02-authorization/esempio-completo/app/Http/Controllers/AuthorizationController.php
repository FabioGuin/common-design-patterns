<?php

namespace App\Http\Controllers;

use App\Services\AuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthorizationController extends Controller
{
    private AuthorizationService $authorizationService;

    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * Check user permissions
     */
    public function checkPermission(Request $request): JsonResponse
    {
        $request->validate([
            'permission' => 'required|string',
            'resource' => 'nullable|string'
        ]);

        try {
            $user = Auth::user();
            $permission = $request->input('permission');
            $resource = $request->input('resource');

            $hasPermission = $this->authorizationService->checkPermission($user, $permission, $resource);

            return response()->json([
                'success' => true,
                'has_permission' => $hasPermission,
                'permission' => $permission,
                'resource' => $resource
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check user role
     */
    public function checkRole(Request $request): JsonResponse
    {
        $request->validate([
            'role' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            $role = $request->input('role');

            $hasRole = $this->authorizationService->checkRole($user, $role);

            return response()->json([
                'success' => true,
                'has_role' => $hasRole,
                'role' => $role
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user permissions
     */
    public function getUserPermissions(): JsonResponse
    {
        try {
            $user = Auth::user();
            $permissions = $this->authorizationService->getUserPermissions($user);

            return response()->json([
                'success' => true,
                'permissions' => $permissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user roles
     */
    public function getUserRoles(): JsonResponse
    {
        try {
            $user = Auth::user();
            $roles = $this->authorizationService->getUserRoles($user);

            return response()->json([
                'success' => true,
                'roles' => $roles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'required|string'
        ]);

        try {
            $userId = $request->input('user_id');
            $role = $request->input('role');

            $this->authorizationService->assignRole($userId, $role);

            return response()->json([
                'success' => true,
                'message' => 'Role assigned successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'required|string'
        ]);

        try {
            $userId = $request->input('user_id');
            $role = $request->input('role');

            $this->authorizationService->removeRole($userId, $role);

            return response()->json([
                'success' => true,
                'message' => 'Role removed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authorization statistics
     */
    public function getAuthorizationStats(): JsonResponse
    {
        try {
            $stats = $this->authorizationService->getAuthorizationStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get authorization statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
