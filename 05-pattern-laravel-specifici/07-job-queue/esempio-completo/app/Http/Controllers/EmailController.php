<?php

namespace App\Http\Controllers;

use App\Jobs\SendWelcomeEmailJob;
use App\Jobs\SendNewsletterJob;
use App\Jobs\SendNotificationJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{
    public function index()
    {
        $users = User::all();
        $failedJobs = \DB::table('failed_jobs')->count();
        $pendingJobs = \DB::table('jobs')->count();

        return view('email-demo', compact('users', 'failedJobs', 'pendingJobs'));
    }

    public function registerUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        try {
            // Crea l'utente
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Dispatch job per email di benvenuto
            SendWelcomeEmailJob::dispatch($user, $request->email);

            Log::info('Utente registrato e job di benvenuto dispatchato', [
                'user_id' => $user->id,
                'email' => $request->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Utente registrato con successo! Email di benvenuto in arrivo.',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Errore nella registrazione utente', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore nella registrazione: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendNewsletter(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        try {
            $users = User::all();
            $newsletterData = [
                'id' => uniqid(),
                'subject' => $request->subject,
                'content' => $request->content,
                'sent_at' => now()
            ];

            $dispatchedCount = 0;
            foreach ($users as $user) {
                SendNewsletterJob::dispatch($user, $newsletterData);
                $dispatchedCount++;
            }

            Log::info('Newsletter dispatchata a tutti gli utenti', [
                'newsletter_id' => $newsletterData['id'],
                'users_count' => $dispatchedCount,
                'subject' => $request->subject
            ]);

            return response()->json([
                'success' => true,
                'message' => "Newsletter inviata a {$dispatchedCount} utenti!",
                'newsletter' => $newsletterData
            ]);

        } catch (\Exception $e) {
            Log::error('Errore nell\'invio newsletter', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'invio newsletter: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            
            SendNotificationJob::dispatch(
                $user,
                $request->title,
                $request->message,
                $request->get('data', [])
            );

            Log::info('Notifica dispatchata', [
                'user_id' => $user->id,
                'title' => $request->title
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notifica inviata con successo!',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Errore nell\'invio notifica', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'invio notifica: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getQueueStatus()
    {
        try {
            $pendingJobs = \DB::table('jobs')->count();
            $failedJobs = \DB::table('failed_jobs')->count();
            $users = User::count();

            return response()->json([
                'success' => true,
                'data' => [
                    'pending_jobs' => $pendingJobs,
                    'failed_jobs' => $failedJobs,
                    'total_users' => $users,
                    'last_updated' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dello stato: ' . $e->getMessage()
            ], 500);
        }
    }
}
