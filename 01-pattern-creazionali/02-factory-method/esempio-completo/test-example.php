<?php

require_once 'vendor/autoload.php';

use App\Services\UserFactory\AdminUserFactory;
use App\Services\UserFactory\RegularUserFactory;
use App\Services\UserFactory\GuestUserFactory;

echo "=== Factory Method Pattern Demo ===\n\n";

// Test Admin Factory
echo "1. Creating Admin User:\n";
$adminFactory = new AdminUserFactory();
$admin = $adminFactory->createUser([
    'name' => 'John Admin',
    'email' => 'admin@example.com',
    'password' => 'secure123',
    'admin_notes' => 'System administrator'
]);

echo "   - Type: " . $adminFactory->getUserType() . "\n";
echo "   - Permissions: " . implode(', ', $adminFactory->getDefaultPermissions()) . "\n";
echo "   - User created: {$admin->name} ({$admin->email})\n\n";

// Test Regular User Factory
echo "2. Creating Regular User:\n";
$userFactory = new RegularUserFactory();
$user = $userFactory->createUser([
    'name' => 'Jane User',
    'email' => 'user@example.com',
    'password' => 'password123',
    'preferences' => ['theme' => 'dark', 'notifications' => true]
]);

echo "   - Type: " . $userFactory->getUserType() . "\n";
echo "   - Permissions: " . implode(', ', $userFactory->getDefaultPermissions()) . "\n";
echo "   - User created: {$user->name} ({$user->email})\n\n";

// Test Guest Factory
echo "3. Creating Guest User:\n";
$guestFactory = new GuestUserFactory();
$guest = $guestFactory->createUser([
    'name' => 'Guest Visitor',
    'email' => 'guest@example.com',
    'session_id' => 'sess_123456789'
]);

echo "   - Type: " . $guestFactory->getUserType() . "\n";
echo "   - Permissions: " . implode(', ', $guestFactory->getDefaultPermissions()) . "\n";
echo "   - User created: {$guest->name} ({$guest->email})\n\n";

echo "=== Demo Complete ===\n";
echo "All users created with their specific roles and permissions!\n";
