<?php

namespace App\Services;

use App\Models\User;
use App\Services\EventBusService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserService
{
    private EventBusService $eventBus;
    private string $connection = 'user_service';

    public function __construct(EventBusService $eventBus)
    {
        $this->eventBus = $eventBus;
        $this->initializeEventHandlers();
    }

    /**
     * Inizializza i gestori di eventi
     */
    private function initializeEventHandlers(): void
    {
        // Gestisce eventi di creazione ordine
        $this->eventBus->subscribe('OrderCreated', function ($event) {
            $this->handleOrderCreated($event);
        });

        // Gestisce eventi di pagamento
        $this->eventBus->subscribe('PaymentProcessed', function ($event) {
            $this->handlePaymentProcessed($event);
        });
    }

    /**
     * Crea un nuovo utente
     */
    public function createUser(array $userData): array
    {
        return DB::connection($this->connection)->transaction(function () use ($userData) {
            $user = new User();
            $user->name = $userData['name'];
            $user->email = $userData['email'];
            $user->created_at = now();
            $user->save();

            // Pubblica evento
            $this->eventBus->publish('UserCreated', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at
            ]);

            Log::info("User created", [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'database' => $this->connection
            ];
        });
    }

    /**
     * Ottiene un utente per ID
     */
    public function getUser(int $userId): ?array
    {
        $user = User::on($this->connection)->find($userId);
        
        if (!$user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'database' => $this->connection
        ];
    }

    /**
     * Ottiene tutti gli utenti
     */
    public function getAllUsers(): array
    {
        $users = User::on($this->connection)->all();
        
        return $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'database' => $this->connection
            ];
        })->toArray();
    }

    /**
     * Aggiorna un utente
     */
    public function updateUser(int $userId, array $userData): ?array
    {
        return DB::connection($this->connection)->transaction(function () use ($userId, $userData) {
            $user = User::on($this->connection)->find($userId);
            
            if (!$user) {
                return null;
            }

            $user->name = $userData['name'] ?? $user->name;
            $user->email = $userData['email'] ?? $user->email;
            $user->updated_at = now();
            $user->save();

            // Pubblica evento
            $this->eventBus->publish('UserUpdated', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'updated_at' => $user->updated_at
            ]);

            Log::info("User updated", [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'database' => $this->connection
            ];
        });
    }

    /**
     * Elimina un utente
     */
    public function deleteUser(int $userId): bool
    {
        return DB::connection($this->connection)->transaction(function () use ($userId) {
            $user = User::on($this->connection)->find($userId);
            
            if (!$user) {
                return false;
            }

            $user->delete();

            // Pubblica evento
            $this->eventBus->publish('UserDeleted', [
                'user_id' => $userId,
                'deleted_at' => now()
            ]);

            Log::info("User deleted", [
                'user_id' => $userId
            ]);

            return true;
        });
    }

    /**
     * Gestisce l'evento di creazione ordine
     */
    private function handleOrderCreated(array $event): void
    {
        $userId = $event['data']['user_id'];
        $orderId = $event['data']['order_id'];

        Log::info("Order created event received", [
            'user_id' => $userId,
            'order_id' => $orderId
        ]);

        // Aggiorna statistiche utente o altre operazioni
        // In un'implementazione reale, potresti aggiornare un contatore ordini
    }

    /**
     * Gestisce l'evento di pagamento processato
     */
    private function handlePaymentProcessed(array $event): void
    {
        $userId = $event['data']['user_id'];
        $paymentId = $event['data']['payment_id'];

        Log::info("Payment processed event received", [
            'user_id' => $userId,
            'payment_id' => $paymentId
        ]);

        // Aggiorna statistiche utente o altre operazioni
        // In un'implementazione reale, potresti aggiornare un contatore pagamenti
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function getStats(): array
    {
        $totalUsers = User::on($this->connection)->count();
        $recentUsers = User::on($this->connection)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return [
            'service' => 'UserService',
            'database' => $this->connection,
            'total_users' => $totalUsers,
            'recent_users' => $recentUsers,
            'connection_status' => $this->testConnection()
        ];
    }

    /**
     * Testa la connessione al database
     */
    private function testConnection(): bool
    {
        try {
            DB::connection($this->connection)->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Ottiene l'ID del pattern per identificazione
     */
    public function getId(): string
    {
        return 'user-service-pattern-' . uniqid();
    }
}
