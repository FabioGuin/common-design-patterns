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
        $users = $this->userService->getUsersWithStats();
        $stats = $this->userService->getUserStats();

        return view('users.index', compact('users', 'stats'));
    }

    /**
     * Mostra un utente specifico
     */
    public function show(int $id): View
    {
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            abort(404, 'Utente non trovato');
        }

        $detailedStats = $this->userService->getUserDetailedStats($id);

        return view('users.show', compact('user', 'detailedStats'));
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
        $data = $request->all();
        
        // Valida i dati
        $errors = $this->userService->validateUserData($data);
        if (!empty($errors)) {
            return redirect()->back()
                           ->withErrors($errors)
                           ->withInput();
        }

        try {
            $user = $this->userService->createUser($data);
            
            return redirect()
                ->route('users.show', $user)
                ->with('success', 'Utente creato con successo!');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Errore durante la creazione dell\'utente: ' . $e->getMessage()])
                           ->withInput();
        }
    }

    /**
     * Mostra il form per modificare un utente
     */
    public function edit(int $id): View
    {
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            abort(404, 'Utente non trovato');
        }

        return view('users.edit', compact('user'));
    }

    /**
     * Aggiorna un utente esistente
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->all();
        
        // Valida i dati
        $errors = $this->userService->validateUserData($data, $id);
        if (!empty($errors)) {
            return redirect()->back()
                           ->withErrors($errors)
                           ->withInput();
        }

        try {
            $success = $this->userService->updateUser($id, $data);
            
            if (!$success) {
                return redirect()->back()
                               ->withErrors(['error' => 'Utente non trovato'])
                               ->withInput();
            }

            return redirect()
                ->route('users.show', $id)
                ->with('success', 'Utente aggiornato con successo!');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Errore durante l\'aggiornamento dell\'utente: ' . $e->getMessage()])
                           ->withInput();
        }
    }

    /**
     * Elimina un utente
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $success = $this->userService->deleteUser($id);
            
            if (!$success) {
                return redirect()->back()
                               ->withErrors(['error' => 'Utente non trovato']);
            }

            return redirect()
                ->route('users.index')
                ->with('success', 'Utente eliminato con successo!');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Errore durante l\'eliminazione dell\'utente: ' . $e->getMessage()]);
        }
    }

    /**
     * Attiva un utente
     */
    public function activate(int $id): RedirectResponse
    {
        try {
            $success = $this->userService->activateUser($id);
            
            if (!$success) {
                return redirect()->back()
                               ->withErrors(['error' => 'Utente non trovato']);
            }

            return redirect()
                ->route('users.show', $id)
                ->with('success', 'Utente attivato con successo!');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Errore durante l\'attivazione dell\'utente: ' . $e->getMessage()]);
        }
    }

    /**
     * Disattiva un utente
     */
    public function deactivate(int $id): RedirectResponse
    {
        try {
            $success = $this->userService->deactivateUser($id);
            
            if (!$success) {
                return redirect()->back()
                               ->withErrors(['error' => 'Utente non trovato']);
            }

            return redirect()
                ->route('users.show', $id)
                ->with('success', 'Utente disattivato con successo!');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Errore durante la disattivazione dell\'utente: ' . $e->getMessage()]);
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
            $success = $this->userService->changeUserRole($id, $request->role);
            
            if (!$success) {
                return redirect()->back()
                               ->withErrors(['error' => 'Utente non trovato o ruolo non valido']);
            }

            return redirect()
                ->route('users.show', $id)
                ->with('success', 'Ruolo utente aggiornato con successo!');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Errore durante il cambio di ruolo: ' . $e->getMessage()]);
        }
    }

    /**
     * Cerca utenti
     */
    public function search(Request $request): View
    {
        $term = $request->get('q', '');
        $users = $this->userService->searchUsers($term);

        return view('users.search', compact('users', 'term'));
    }

    /**
     * Mostra utenti per ruolo
     */
    public function byRole(string $role): View
    {
        $users = $this->userService->getUsersByRole($role);
        
        return view('users.by-role', compact('users', 'role'));
    }

    /**
     * Mostra utenti attivi
     */
    public function active(): View
    {
        $users = $this->userService->getActiveUsers();
        
        return view('users.active', compact('users'));
    }

    /**
     * Mostra utenti inattivi
     */
    public function inactive(): View
    {
        $users = $this->userService->getInactiveUsers();
        
        return view('users.inactive', compact('users'));
    }

    /**
     * Mostra utenti più attivi
     */
    public function mostActive(): View
    {
        $users = $this->userService->getMostActiveUsers(20);
        
        return view('users.most-active', compact('users'));
    }

    /**
     * Mostra statistiche degli utenti
     */
    public function stats(): View
    {
        $stats = $this->userService->getUserStats();
        $usersWithStats = $this->userService->getUsersWithStats();
        
        return view('users.stats', compact('stats', 'usersWithStats'));
    }

    /**
     * API endpoint per utenti
     */
    public function api(Request $request)
    {
        $users = $this->userService->getActiveUsers();
        
        return response()->json([
            'success' => true,
            'data' => $users,
            'count' => $users->count()
        ]);
    }
}
