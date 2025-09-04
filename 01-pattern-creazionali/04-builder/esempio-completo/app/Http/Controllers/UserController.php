<?php

namespace App\Http\Controllers;

use App\Builders\UserBuilder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with(['profile', 'settings', 'roles'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = UserBuilder::create()
                ->withBasicInfo(
                    $request->input('first_name'),
                    $request->input('last_name'),
                    $request->input('email')
                )
                ->withPassword($request->input('password'));

            // Aggiungi profilo se presente
            if ($request->has('profile')) {
                $user->withProfile($request->input('profile'));
            }

            // Aggiungi impostazioni se presenti
            if ($request->has('settings')) {
                $user->withSettings($request->input('settings'));
            }

            // Aggiungi ruoli se presenti
            if ($request->has('roles')) {
                $user->withRoles($request->input('roles'));
            }

            // Aggiungi campi opzionali
            if ($request->has('phone')) {
                $user->withPhone($request->input('phone'));
            }

            if ($request->has('address')) {
                $user->withAddress(
                    $request->input('address'),
                    $request->input('city'),
                    $request->input('postal_code'),
                    $request->input('country')
                );
            }

            if ($request->has('birth_date')) {
                $user->withBirthDate($request->input('birth_date'));
            }

            if ($request->has('is_active')) {
                $user->isActive($request->boolean('is_active'));
            }

            if ($request->boolean('email_verified')) {
                $user->withEmailVerified();
            }

            $createdUser = $user->build();

            return response()->json([
                'success' => true,
                'message' => 'Utente creato con successo',
                'data' => $createdUser->load(['profile', 'settings', 'roles'])
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore di validazione',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione dell\'utente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $user->load(['profile', 'settings', 'roles'])
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        try {
            $user->update($request->only([
                'first_name', 'last_name', 'email', 'phone',
                'address', 'city', 'postal_code', 'country',
                'birth_date', 'is_active'
            ]));

            if ($request->has('profile')) {
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $request->input('profile')
                );
            }

            if ($request->has('settings')) {
                $user->settings()->updateOrCreate(
                    ['user_id' => $user->id],
                    $request->input('settings')
                );
            }

            if ($request->has('roles')) {
                $user->roles()->sync($request->input('roles'));
            }

            return response()->json([
                'success' => true,
                'message' => 'Utente aggiornato con successo',
                'data' => $user->load(['profile', 'settings', 'roles'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento dell\'utente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(User $user): JsonResponse
    {
        try {
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Utente eliminato con successo'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione dell\'utente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createAdmin(Request $request): JsonResponse
    {
        try {
            $user = UserBuilder::create()
                ->withBasicInfo(
                    $request->input('first_name'),
                    $request->input('last_name'),
                    $request->input('email')
                )
                ->withPassword($request->input('password'))
                ->asAdmin()
                ->withEmailVerified()
                ->withProfile([
                    'bio' => 'Amministratore del sistema',
                    'location' => 'Italia'
                ])
                ->withSettings([
                    'notifications' => true,
                    'theme' => 'dark',
                    'language' => 'it'
                ])
                ->build();

            return response()->json([
                'success' => true,
                'message' => 'Amministratore creato con successo',
                'data' => $user->load(['profile', 'settings', 'roles'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione dell\'amministratore',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createEditor(Request $request): JsonResponse
    {
        try {
            $user = UserBuilder::create()
                ->withBasicInfo(
                    $request->input('first_name'),
                    $request->input('last_name'),
                    $request->input('email')
                )
                ->withPassword($request->input('password'))
                ->asEditor()
                ->withEmailVerified()
                ->withProfile([
                    'bio' => $request->input('bio', 'Editor del sistema'),
                    'location' => $request->input('location', 'Italia')
                ])
                ->withSettings([
                    'notifications' => true,
                    'theme' => 'light',
                    'language' => 'it'
                ])
                ->build();

            return response()->json([
                'success' => true,
                'message' => 'Editor creato con successo',
                'data' => $user->load(['profile', 'settings', 'roles'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione dell\'editor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
