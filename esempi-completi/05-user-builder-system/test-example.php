<?php

require_once 'vendor/autoload.php';

use App\Builders\UserBuilder;

// Simula l'ambiente Laravel per il test
echo "=== ESEMPIO BUILDER PATTERN ===\n\n";

echo "1. Creazione utente base:\n";
$user1 = UserBuilder::create()
    ->withBasicInfo('Mario', 'Rossi', 'mario@example.com')
    ->withPassword('password123')
    ->build();

echo "Utente creato: {$user1->first_name} {$user1->last_name} ({$user1->email})\n\n";

echo "2. Creazione utente con profilo:\n";
$user2 = UserBuilder::create()
    ->withBasicInfo('Giulia', 'Bianchi', 'giulia@example.com')
    ->withPassword('password123')
    ->withProfile([
        'bio' => 'Sviluppatrice Laravel esperta',
        'avatar' => 'giulia-avatar.jpg',
        'location' => 'Milano, Italia'
    ])
    ->build();

echo "Utente creato: {$user2->first_name} {$user2->last_name}\n";
echo "Profilo: {$user2->profile->bio}\n\n";

echo "3. Creazione utente completo:\n";
$user3 = UserBuilder::create()
    ->withBasicInfo('Luca', 'Verdi', 'luca@example.com')
    ->withPassword('password123')
    ->withPhone('+39 123 456 7890')
    ->withAddress('Via Roma 1', 'Milano', '20100', 'Italia')
    ->withBirthDate('1985-05-15')
    ->withEmailVerified()
    ->withProfile([
        'bio' => 'Sviluppatore full-stack',
        'avatar' => 'luca-avatar.jpg',
        'website' => 'https://lucaverdi.dev'
    ])
    ->withSettings([
        'notifications' => true,
        'theme' => 'dark',
        'language' => 'it'
    ])
    ->asAdmin()
    ->build();

echo "Utente creato: {$user3->first_name} {$user3->last_name}\n";
echo "Telefono: {$user3->phone}\n";
echo "Indirizzo: {$user3->address}, {$user3->city}\n";
echo "Ruolo: " . ($user3->isAdmin() ? 'Admin' : 'User') . "\n";
echo "Impostazioni: Tema {$user3->settings->theme}, Notifiche " . ($user3->settings->notifications ? 'ON' : 'OFF') . "\n\n";

echo "4. Creazione utente con ruoli multipli:\n";
$user4 = UserBuilder::create()
    ->withBasicInfo('Anna', 'Neri', 'anna@example.com')
    ->withPassword('password123')
    ->withRoles(['admin', 'editor'])
    ->withProfile(['bio' => 'Amministratrice e editor'])
    ->build();

echo "Utente creato: {$user4->first_name} {$user4->last_name}\n";
echo "Ruoli: " . $user4->roles->pluck('name')->join(', ') . "\n\n";

echo "5. Test validazione (dovrebbe fallire):\n";
try {
    $user5 = UserBuilder::create()
        ->withBasicInfo('', 'Rossi', 'email-invalido')
        ->withPassword('123')
        ->build();
} catch (Exception $e) {
    echo "Validazione fallita come previsto: " . $e->getMessage() . "\n\n";
}

echo "6. Test dati del builder:\n";
$builder = UserBuilder::create()
    ->withBasicInfo('Marco', 'Blu', 'marco@example.com')
    ->withPassword('password123')
    ->withProfile(['bio' => 'Test bio'])
    ->asEditor();

$data = $builder->getData();
echo "Dati del builder:\n";
echo "- Nome: {$data['user']['first_name']} {$data['user']['last_name']}\n";
echo "- Email: {$data['user']['email']}\n";
echo "- Bio: {$data['profile']['bio']}\n";
echo "- Ruoli: " . implode(', ', $data['roles']) . "\n\n";

echo "=== FINE ESEMPIO ===\n";
