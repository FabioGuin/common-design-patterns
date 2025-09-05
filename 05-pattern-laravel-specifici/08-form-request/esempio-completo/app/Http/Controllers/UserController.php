<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = User::all();
        return view('form-request-demo', compact('users'));
    }

    public function store(CreateUserRequest $request)
    {
        try {
            $userData = $request->validated();
            $user = $this->userService->createUser($userData);

            Log::info('Utente creato con successo', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Utente creato con successo!',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Errore nella creazione utente', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione utente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $userData = $request->validated();
            $this->userService->updateUser($user, $userData);

            Log::info('Utente aggiornato con successo', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($userData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Utente aggiornato con successo!',
                'user' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Errore nell\'aggiornamento utente', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento utente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function destroy(User $user)
    {
        try {
            // Controllo autorizzazione semplice
            if (auth()->user()->role !== 'admin' && auth()->user()->id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non hai i permessi per eliminare questo utente.'
                ], 403);
            }

            $this->userService->deleteUser($user);

            Log::info('Utente eliminato', [
                'user_id' => $user->id,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Utente eliminato con successo!'
            ]);

        } catch (\Exception $e) {
            Log::error('Errore nell\'eliminazione utente', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'eliminazione utente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getValidationRules(Request $request)
    {
        $action = $request->get('action', 'create');
        
        $rules = match($action) {
            'create' => (new CreateUserRequest())->rules(),
            'update' => (new UpdateUserRequest())->rules(),
            default => []
        };

        return response()->json([
            'success' => true,
            'rules' => $rules
        ]);
    }
}
