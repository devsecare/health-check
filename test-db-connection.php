<?php

/**
 * Simple database connection test script
 * Run: php test-db-connection.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $connection = DB::connection()->getPdo();
    echo "✅ Database connection successful!\n";
    echo "Connected to: " . config('database.connections.mysql.database') . "\n";
    echo "Host: " . config('database.connections.mysql.host') . "\n";
    echo "Port: " . config('database.connections.mysql.port') . "\n";
    
    // Test a simple query
    $result = DB::select('SELECT DATABASE() as db');
    echo "Current database: " . $result[0]->db . "\n";
    
    // Show tables
    $tables = DB::select('SHOW TABLES');
    echo "Tables in database: " . count($tables) . "\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. MySQL server is running\n";
    echo "2. Database credentials in .env file\n";
    echo "3. Database 'ecare_health_checker' exists\n";
    echo "4. User 'ecare_health_checker_ai' has proper permissions\n";
    echo "\nTo start MySQL (macOS): brew services start mysql\n";
}

