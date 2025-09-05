<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\UserFactory;

class UserController extends Controller
{
    /**
     * Endpoint principale per testare il Factory Method
     */
    public function index(Request $request)
    {
        $supportedRoles = UserFactory::getSupportedRoles();
        
        return response()->json([
            'success' => true,
            'message' => 'Factory Method Pattern Demo',
            'data' => [
                'supported_roles' => $supportedRoles,
                'pattern_description' => 'Factory Method crea oggetti senza specificare le loro classi concrete'
            ]
        ]);
    }

    /**
     * Endpoint di test per dimostrare la creazione di utenti
     */
    public function test()
    {
        $users = [];
        $roles = UserFactory::getSupportedRoles();
        
        foreach ($roles as $role) {
            $users[] = UserFactory::createUser(
                $role,
                ucfirst($role) . ' User',
                strtolower($role) . '@example.com'
            )->toArray();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Factory Method Test Completed',
            'data' => [
                'users_created' => count($users),
                'users' => $users,
                'pattern_benefits' => [
                    'Encapsulation' => 'Logica di creazione incapsulata',
                    'Flexibility' => 'Facile aggiungere nuovi tipi',
                    'Consistency' => 'Creazione consistente per ogni tipo',
                    'Maintainability' => 'Codice più mantenibile'
                ]
            ]
        ]);
    }

    /**
     * Endpoint per creare un utente specifico
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'role' => 'required|string|in:' . implode(',', UserFactory::getSupportedRoles()),
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255'
        ]);

        try {
            $user = UserFactory::createUser(
                $request->input('role'),
                $request->input('name'),
                $request->input('email')
            );

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating user: ' . $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Endpoint per mostrare la vista di esempio
     */
    public function show()
    {
        $supportedRoles = UserFactory::getSupportedRoles();
        
        return view('factory-method.example', compact('supportedRoles'));
    }
}
