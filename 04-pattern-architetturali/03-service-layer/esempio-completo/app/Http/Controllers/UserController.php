<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Mostra la lista degli utenti
     */
    public function index(Request $request): View
    {
        try {
            $filters = $request->only(['role', 'is_active', 'search']);
            $users = $this->userService->getUsers($filters);
            $stats = $this->userService->getUserStats();

            return view('users.index', compact('users', 'stats'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mostra un utente specifico
     */
    public function show(int $id): View
    {
        try {
            $user = $this->userService->getUserById($id);
            $detailedStats = $this->userService->getUserDetailedStats($id);

            return view('users.show', compact('user', 'detailedStats'));
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Mostra il form per creare un nuovo utente
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Salva un nuovo utente
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $user = $this->userService->createUser($request->all());
            
            return redirect()
                ->route('users.show', $user)
                ->with('success', 'Utente creato con successo!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Mostra il form per modificare un utente
     */
    public function edit(int $id): View
    {
        try {
            $user = $this->userService->getUserById($id);
            return view('users.edit', compact('user'));
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Aggiorna un utente esistente
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            $user = $this->userService->updateUser($id, $request->all());
            
            return redirect()
                ->route('users.show', $user)
                ->with('success', 'Utente aggiornato con successo!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Elimina un utente
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->userService->deleteUser($id);
            
            return redirect()
                ->route('users.index')
                ->with('success', 'Utente eliminato con successo!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Attiva un utente
     */
    public function activate(int $id): RedirectResponse
    {
        try {
            $user = $this->userService->activateUser($id);
            
            return redirect()
                ->route('users.show', $user)
                ->with('success', 'Utente attivato con successo!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Disattiva un utente
     */
    public function deactivate(int $id): RedirectResponse
    {
        try {
            $user = $this->userService->deactivateUser($id);
            
            return redirect()
                ->route('users.show', $user)
                ->with('success', 'Utente disattivato con successo!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cambia il ruolo di un utente
     */
    public function changeRole(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'role' => 'required|in:user,editor,admin'
        ]);

        try {
            $user = $this->userService->changeUserRole($id, $request->role);
            
            return redirect()
                ->route('users.show', $user)
                ->with('success', 'Ruolo utente aggiornato con successo!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cerca utenti
     */
    public function search(Request $request): View
    {
        try {
            $filters = ['search' => $request->get('q')];
            $users = $this->userService->getUsers($filters);
            $term = $request->get('q');

            return view('users.search', compact('users', 'term'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mostra utenti per ruolo
     */
    public function byRole(string $role): View
    {
        try {
            $users = $this->userService->getUsersByRole($role);
            return view('users.by-role', compact('users', 'role'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mostra utenti attivi
     */
    public function active(): View
    {
        try {
            $users = $this->userService->getActiveUsers();
            return view('users.active', compact('users'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mostra utenti più attivi
     */
    public function mostActive(): View
    {
        try {
            $users = $this->userService->getMostActiveUsers(20);
            return view('users.most-active', compact('users'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mostra statistiche degli utenti
     */
    public function stats(): View
    {
        try {
            $stats = $this->userService->getUserStats();
            return view('users.stats', compact('stats'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * API endpoint per utenti
     */
    public function api(Request $request)
    {
        try {
            $filters = $request->only(['role', 'is_active', 'search']);
            $users = $this->userService->getUsers($filters);
            
            return response()->json([
                'success' => true,
                'data' => $users,
                'count' => $users->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
