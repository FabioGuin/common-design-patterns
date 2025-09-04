<?php

use App\Builders\UserBuilder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crea i ruoli necessari per i test
    Role::create(['name' => 'admin', 'description' => 'Admin']);
    Role::create(['name' => 'editor', 'description' => 'Editor']);
    Role::create(['name' => 'user', 'description' => 'User']);
});

test('può creare un utente base con Builder', function () {
    $user = UserBuilder::create()
        ->withBasicInfo('Mario', 'Rossi', 'mario@example.com')
        ->withPassword('password123')
        ->build();

    expect($user)->toBeInstanceOf(User::class);
    expect($user->first_name)->toBe('Mario');
    expect($user->last_name)->toBe('Rossi');
    expect($user->email)->toBe('mario@example.com');
    expect($user->password)->not->toBe('password123'); // Dovrebbe essere hashato
});

test('può creare un utente con profilo', function () {
    $user = UserBuilder::create()
        ->withBasicInfo('Giulia', 'Bianchi', 'giulia@example.com')
        ->withPassword('password123')
        ->withProfile([
            'bio' => 'Sviluppatrice Laravel',
            'avatar' => 'avatar.jpg',
            'location' => 'Milano'
        ])
        ->build();

    expect($user->profile)->not->toBeNull();
    expect($user->profile->bio)->toBe('Sviluppatrice Laravel');
    expect($user->profile->avatar)->toBe('avatar.jpg');
    expect($user->profile->location)->toBe('Milano');
});

test('può creare un utente con impostazioni', function () {
    $user = UserBuilder::create()
        ->withBasicInfo('Luca', 'Verdi', 'luca@example.com')
        ->withPassword('password123')
        ->withSettings([
            'notifications' => true,
            'theme' => 'dark',
            'language' => 'it'
        ])
        ->build();

    expect($user->settings)->not->toBeNull();
    expect($user->settings->notifications)->toBeTrue();
    expect($user->settings->theme)->toBe('dark');
    expect($user->settings->language)->toBe('it');
});

test('può creare un utente con ruoli', function () {
    $user = UserBuilder::create()
        ->withBasicInfo('Anna', 'Neri', 'anna@example.com')
        ->withPassword('password123')
        ->asAdmin()
        ->build();

    expect($user->roles)->toHaveCount(1);
    expect($user->roles->first()->name)->toBe('admin');
    expect($user->isAdmin())->toBeTrue();
});

test('può creare un utente con ruoli multipli', function () {
    $user = UserBuilder::create()
        ->withBasicInfo('Marco', 'Blu', 'marco@example.com')
        ->withPassword('password123')
        ->withRoles(['admin', 'editor'])
        ->build();

    expect($user->roles)->toHaveCount(2);
    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('editor'))->toBeTrue();
});

test('può creare un utente completo con tutti i campi', function () {
    $user = UserBuilder::create()
        ->withBasicInfo('Paolo', 'Gialli', 'paolo@example.com')
        ->withPassword('password123')
        ->withPhone('+39 123 456 7890')
        ->withAddress('Via Roma 1', 'Milano', '20100', 'Italia')
        ->withBirthDate('1985-05-15')
        ->withEmailVerified()
        ->isActive(false)
        ->withProfile([
            'bio' => 'Sviluppatore esperto',
            'avatar' => 'paolo.jpg'
        ])
        ->withSettings([
            'notifications' => true,
            'theme' => 'dark'
        ])
        ->asEditor()
        ->build();

    expect($user->phone)->toBe('+39 123 456 7890');
    expect($user->address)->toBe('Via Roma 1');
    expect($user->city)->toBe('Milano');
    expect($user->postal_code)->toBe('20100');
    expect($user->country)->toBe('Italia');
    expect($user->birth_date->format('Y-m-d'))->toBe('1985-05-15');
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->is_active)->toBeFalse();
    expect($user->profile->bio)->toBe('Sviluppatore esperto');
    expect($user->settings->notifications)->toBeTrue();
    expect($user->isEditor())->toBeTrue();
});

test('valida i dati durante la costruzione', function () {
    expect(function () {
        UserBuilder::create()
            ->withBasicInfo('', 'Rossi', 'email-invalido')
            ->withPassword('123') // Password troppo corta
            ->build();
    })->toThrow(ValidationException::class);
});

test('valida i dati del profilo', function () {
    expect(function () {
        UserBuilder::create()
            ->withBasicInfo('Mario', 'Rossi', 'mario@example.com')
            ->withPassword('password123')
            ->withProfile([
                'bio' => str_repeat('a', 1001), // Bio troppo lunga
                'website' => 'url-invalido' // URL non valido
            ])
            ->build();
    })->toThrow(ValidationException::class);
});

test('valida i dati delle impostazioni', function () {
    expect(function () {
        UserBuilder::create()
            ->withBasicInfo('Mario', 'Rossi', 'mario@example.com')
            ->withPassword('password123')
            ->withSettings([
                'theme' => 'colore-invalido', // Tema non valido
                'language' => 'lingua-invalida' // Lingua non valida
            ])
            ->build();
    })->toThrow(ValidationException::class);
});

test('può ottenere i dati del builder prima della costruzione', function () {
    $builder = UserBuilder::create()
        ->withBasicInfo('Mario', 'Rossi', 'mario@example.com')
        ->withPassword('password123')
        ->withProfile(['bio' => 'Test bio'])
        ->withSettings(['theme' => 'dark'])
        ->asAdmin();

    $data = $builder->getData();

    expect($data['user']['first_name'])->toBe('Mario');
    expect($data['user']['last_name'])->toBe('Rossi');
    expect($data['user']['email'])->toBe('mario@example.com');
    expect($data['profile']['bio'])->toBe('Test bio');
    expect($data['settings']['theme'])->toBe('dark');
    expect($data['roles'])->toContain('admin');
});

test('può creare utenti con metodi di convenienza', function () {
    $admin = UserBuilder::create()
        ->withBasicInfo('Admin', 'User', 'admin@example.com')
        ->withPassword('password123')
        ->asAdmin()
        ->build();

    $editor = UserBuilder::create()
        ->withBasicInfo('Editor', 'User', 'editor@example.com')
        ->withPassword('password123')
        ->asEditor()
        ->build();

    $user = UserBuilder::create()
        ->withBasicInfo('Standard', 'User', 'user@example.com')
        ->withPassword('password123')
        ->asUser()
        ->build();

    expect($admin->isAdmin())->toBeTrue();
    expect($editor->isEditor())->toBeTrue();
    expect($user->hasRole('user'))->toBeTrue();
});

test('può creare utenti con dati personalizzati', function () {
    $user = UserBuilder::create()
        ->withBasicInfo('Mario', 'Rossi', 'mario@example.com')
        ->withPassword('password123')
        ->withCustomData([
            'phone' => '+39 123 456 7890',
            'is_active' => false
        ])
        ->build();

    expect($user->phone)->toBe('+39 123 456 7890');
    expect($user->is_active)->toBeFalse();
});
