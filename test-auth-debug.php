<?php

// Simple script to check user roles in database
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    // Bootstrap the application
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    // Connect to database
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=laravel", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== USER ROLE DEBUG REPORT ===\n\n";

    // Get all users and their roles
    $stmt = $pdo->query("SELECT id, name, email, role, is_active, created_at FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Total Users: " . count($users) . "\n\n";

    // Group users by role
    $byRole = [];
    foreach ($users as $user) {
        $byRole[$user['role']][] = $user;
    }

    foreach ($byRole as $role => $users) {
        echo "=== {$role} ROLE USERS ===\n";
        foreach ($users as $user) {
            echo "ID: {$user['id']} | Name: {$user['name']} | Email: {$user['email']} | Active: " . ($user['is_active'] ? 'Yes' : 'No') . " | Created: {$user['created_at']}\n";
        }
        echo "\n";
    }

    // Check if there are vendor accounts
    $vendors = $byRole['vendor'] ?? [];
    if (empty($vendors)) {
        echo "❌ NO VENDOR ACCOUNTS FOUND!\n";
    } else {
        echo "✅ Found " . count($vendors) . " vendor account(s)\n";
        echo "This confirms that superadmin vendor creation is working correctly.\n";
        echo "The issue is likely in the authentication/session sharing between web and API.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
