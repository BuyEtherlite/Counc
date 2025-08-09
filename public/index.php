
<?php

define('LARAVEL_START', microtime(true));

use Illuminate\Http\Request;

// Pre-bootstrap installation checks
function checkInstallationRequirements() {
    $basePath = __DIR__ . '/..';
    $issues = [];

    // Check if .env file exists
    if (!file_exists($basePath . '/.env')) {
        $issues[] = 'missing_env';
    }

    // Check if vendor directory exists
    if (!is_dir($basePath . '/vendor')) {
        $issues[] = 'missing_vendor';
    }

    // Check if storage is writable
    if (!is_writable($basePath . '/storage')) {
        $issues[] = 'storage_not_writable';
    }

    return $issues;
}

// Handle pre-installation issues
function handlePreInstallation($issues) {
    $basePath = __DIR__ . '/..';

    // If .env doesn't exist, create a minimal one
    if (in_array('missing_env', $issues)) {
        $envContent = "APP_NAME=\"Council ERP\"\n";
        $envContent .= "APP_ENV=local\n";
        $envContent .= "APP_DEBUG=true\n";
        $envContent .= "APP_KEY=\n";
        $envContent .= "APP_URL=http://localhost\n\n";
        $envContent .= "DB_CONNECTION=mysql\n";
        $envContent .= "DB_HOST=127.0.0.1\n";
        $envContent .= "DB_PORT=3306\n";
        $envContent .= "DB_DATABASE=council_erp\n";
        $envContent .= "DB_USERNAME=root\n";
        $envContent .= "DB_PASSWORD=\n";

        @file_put_contents($basePath . '/.env', $envContent);
    }

    // If vendor is missing, show installation instructions
    if (in_array('missing_vendor', $issues)) {
        showInstallationError('missing_dependencies');
        exit;
    }

    // If storage is not writable, show error
    if (in_array('storage_not_writable', $issues)) {
        showInstallationError('storage_permissions');
        exit;
    }
}

function showInstallationError($type) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Council ERP - Installation Required</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
            .error-box { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <h1>🏛️ Council ERP - Installation Required</h1>";

    if ($type === 'missing_dependencies') {
        echo "<div class='error-box'>
            <strong>Missing Dependencies:</strong> Please run 'composer install' to install required dependencies.
        </div>";
    } elseif ($type === 'storage_permissions') {
        echo "<div class='error-box'>
            <strong>Storage Permissions:</strong> The storage directory is not writable. Please set proper permissions.
        </div>";
    }

    echo "</body></html>";
}

// Basic pre-installation check
if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
    showInstallationError('missing_dependencies');
    exit;
}

// Check if .env exists, create basic one if not
$envPath = __DIR__.'/../.env';
if (!file_exists($envPath)) {
    $envContent = "APP_NAME=\"Council ERP\"\n";
    $envContent .= "APP_ENV=production\n";
    $envContent .= "APP_DEBUG=false\n";
    $envContent .= "APP_KEY=\n";
    $envContent .= "APP_URL=https://yourdomain.com\n\n";
    $envContent .= "DB_CONNECTION=mysql\n";
    $envContent .= "DB_HOST=localhost\n";
    $envContent .= "DB_PORT=3306\n";
    $envContent .= "DB_DATABASE=your_database_name\n";
    $envContent .= "DB_USERNAME=your_database_user\n";
    $envContent .= "DB_PASSWORD=your_database_password\n";
    
    // Ensure the directory is writable
    if (is_writable(dirname($envPath))) {
        @file_put_contents($envPath, $envContent);
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
