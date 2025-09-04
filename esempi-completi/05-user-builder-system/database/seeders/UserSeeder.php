<?php

namespace Database\Seeders;

use App\Builders\UserBuilder;
use App\Models\Role;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Crea i ruoli
        $roles = [
            ['name' => 'admin', 'description' => 'Amministratore del sistema', 'permissions' => ['*']],
            ['name' => 'editor', 'description' => 'Editor di contenuti', 'permissions' => ['create', 'read', 'update']],
            ['name' => 'user', 'description' => 'Utente standard', 'permissions' => ['read']],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        // Crea utenti di esempio usando il Builder
        $this->createAdminUser();
        $this->createEditorUser();
        $this->createStandardUser();
        $this->createComplexUser();
    }

    private function createAdminUser(): void
    {
        UserBuilder::create()
            ->withBasicInfo('Mario', 'Rossi', 'admin@example.com')
            ->withPassword('password123')
            ->asAdmin()
            ->withEmailVerified()
            ->withPhone('+39 123 456 7890')
            ->withAddress('Via Roma 1', 'Milano', '20100', 'Italia')
            ->withBirthDate('1985-05-15')
            ->withProfile([
                'bio' => 'Amministratore del sistema con esperienza in Laravel e PHP',
                'avatar' => 'admin-avatar.jpg',
                'website' => 'https://mariorossi.dev',
                'location' => 'Milano, Italia',
                'social_links' => [
                    'twitter' => '@mariorossi',
                    'linkedin' => 'mario-rossi-dev'
                ]
            ])
            ->withSettings([
                'notifications' => true,
                'theme' => 'dark',
                'language' => 'it',
                'timezone' => 'Europe/Rome',
                'custom_settings' => [
                    'dashboard_widgets' => ['stats', 'recent_activity', 'notifications'],
                    'email_frequency' => 'daily'
                ]
            ])
            ->build();
    }

    private function createEditorUser(): void
    {
        UserBuilder::create()
            ->withBasicInfo('Giulia', 'Bianchi', 'editor@example.com')
            ->withPassword('password123')
            ->asEditor()
            ->withEmailVerified()
            ->withPhone('+39 098 765 4321')
            ->withAddress('Via Milano 2', 'Roma', '00100', 'Italia')
            ->withBirthDate('1990-08-22')
            ->withProfile([
                'bio' => 'Editor di contenuti specializzata in tecnologia e web development',
                'avatar' => 'editor-avatar.jpg',
                'website' => 'https://giuliabianchi.com',
                'location' => 'Roma, Italia',
                'social_links' => [
                    'twitter' => '@giuliabianchi',
                    'instagram' => 'giulia_bianchi'
                ]
            ])
            ->withSettings([
                'notifications' => true,
                'theme' => 'light',
                'language' => 'it',
                'timezone' => 'Europe/Rome',
                'custom_settings' => [
                    'editor_theme' => 'monokai',
                    'auto_save' => true
                ]
            ])
            ->build();
    }

    private function createStandardUser(): void
    {
        UserBuilder::create()
            ->withBasicInfo('Luca', 'Verdi', 'user@example.com')
            ->withPassword('password123')
            ->asUser()
            ->withEmailVerified()
            ->withProfile([
                'bio' => 'Utente appassionato di tecnologia',
                'location' => 'Napoli, Italia'
            ])
            ->withSettings([
                'notifications' => false,
                'theme' => 'auto',
                'language' => 'it'
            ])
            ->build();
    }

    private function createComplexUser(): void
    {
        UserBuilder::create()
            ->withBasicInfo('Anna', 'Neri', 'anna@example.com')
            ->withPassword('password123')
            ->withRoles(['editor', 'user'])
            ->withEmailVerified()
            ->withPhone('+39 333 123 4567')
            ->withAddress('Via Napoli 3', 'Firenze', '50100', 'Italia')
            ->withBirthDate('1988-12-10')
            ->isActive(false)
            ->withProfile([
                'bio' => 'Sviluppatrice full-stack con passione per il design',
                'avatar' => 'anna-avatar.jpg',
                'website' => 'https://annaneri.dev',
                'location' => 'Firenze, Italia',
                'social_links' => [
                    'github' => 'annaneri',
                    'twitter' => '@annaneri',
                    'linkedin' => 'anna-neri-dev'
                ]
            ])
            ->withSettings([
                'notifications' => true,
                'theme' => 'dark',
                'language' => 'en',
                'timezone' => 'Europe/London',
                'custom_settings' => [
                    'code_theme' => 'dracula',
                    'font_size' => '14px',
                    'auto_complete' => true
                ]
            ])
            ->build();
    }
}
