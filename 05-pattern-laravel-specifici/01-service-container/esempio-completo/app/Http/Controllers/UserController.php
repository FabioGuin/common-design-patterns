<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\EmailService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
        private EmailService $emailService,
        private CacheService $cacheService
    ) {
        Log::info('UserController: Dependencies injected via Service Container');
    }

    /**
     * Mostra la lista degli utenti
     */
    public function index(Request $request): View
    {
        Log::info('UserController: Displaying users list');

        $users = $this->userService->getAllUsers();
        $stats = $this->userService->getUserStats();

        return view('users.index', compact('users', 'stats'));
    }

    /**
     * Mostra un utente specifico
     */
    public function show(int $id): View
    {
        Log::info('UserController: Displaying user', ['user_id' => $id]);

        $user = $this->userService->getUserById($id);

        return view('users.show', compact('user'));
    }

    /**
     * Mostra il form per creare un nuovo utente
     */
    public function create(): View
    {
        Log::info('UserController: Displaying create user form');

        return view('users.create');
    }

    /**
     * Salva un nuovo utente
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        try {
            Log::info('UserController: Creating user', ['email' => $request->email]);

            $user = $this->userService->createUser($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Utente creato con successo!',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            Log::error('UserController: Failed to create user', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione dell\'utente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostra il form per modificare un utente
     */
    public function edit(int $id): View
    {
        Log::info('UserController: Displaying edit user form', ['user_id' => $id]);

        $user = $this->userService->getUserById($id);

        return view('users.edit', compact('user'));
    }

    /**
     * Aggiorna un utente esistente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
        ]);

        try {
            Log::info('UserController: Updating user', ['user_id' => $id]);

            $user = $this->userService->updateUser($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Utente aggiornato con successo!',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            Log::error('UserController: Failed to update user', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento dell\'utente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un utente
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            Log::info('UserController: Deleting user', ['user_id' => $id]);

            $this->userService->deleteUser($id);

            return response()->json([
                'success' => true,
                'message' => 'Utente eliminato con successo!'
            ], 200);
        } catch (\Exception $e) {
            Log::error('UserController: Failed to delete user', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione dell\'utente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Attiva un utente
     */
    public function activate(int $id): JsonResponse
    {
        try {
            Log::info('UserController: Activating user', ['user_id' => $id]);

            $user = $this->userService->activateUser($id);

            return response()->json([
                'success' => true,
                'message' => 'Utente attivato con successo!',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            Log::error('UserController: Failed to activate user', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'attivazione dell\'utente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disattiva un utente
     */
    public function deactivate(int $id): JsonResponse
    {
        try {
            Log::info('UserController: Deactivating user', ['user_id' => $id]);

            $user = $this->userService->deactivateUser($id);

            return response()->json([
                'success' => true,
                'message' => 'Utente disattivato con successo!',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            Log::error('UserController: Failed to deactivate user', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante la disattivazione dell\'utente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cerca utenti
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255'
        ]);

        try {
            Log::info('UserController: Searching users', ['query' => $request->q]);

            $users = $this->userService->searchUsers($request->q);

            return response()->json([
                'success' => true,
                'data' => $users,
                'count' => $users->count(),
                'query' => $request->q
            ], 200);
        } catch (\Exception $e) {
            Log::error('UserController: Failed to search users', [
                'query' => $request->q,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante la ricerca degli utenti',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recupera statistiche degli utenti
     */
    public function stats(): JsonResponse
    {
        try {
            Log::info('UserController: Retrieving user statistics');

            $stats = $this->userService->getUserStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            Log::error('UserController: Failed to retrieve user statistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero delle statistiche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pulisce la cache degli utenti
     */
    public function clearCache(): JsonResponse
    {
        try {
            Log::info('UserController: Clearing user cache');

            $this->userService->clearUserCache();

            return response()->json([
                'success' => true,
                'message' => 'Cache degli utenti pulita con successo!'
            ], 200);
        } catch (\Exception $e) {
            Log::error('UserController: Failed to clear user cache', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante la pulizia della cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testa i servizi del container
     */
    public function testServices(): JsonResponse
    {
        try {
            Log::info('UserController: Testing services');

            $services = [
                'userService' => $this->userService,
                'emailService' => $this->emailService,
                'cacheService' => $this->cacheService
            ];

            $results = [];
            foreach ($services as $name => $service) {
                $results[$name] = [
                    'class' => get_class($service),
                    'is_working' => method_exists($service, 'getInfo') ? true : false
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Test servizi completato',
                'data' => $results
            ], 200);
        } catch (\Exception $e) {
            Log::error('UserController: Failed to test services', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante il test dei servizi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
