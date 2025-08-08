<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

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

// Pre-installation checks
$issues = checkInstallationRequirements();
if (!empty($issues)) {
    handlePreInstallation($issues);
}

// Catch Laravel bootstrap errors and redirect to install
try {
    // Bootstrap Laravel and handle the request...
    (require_once __DIR__.'/../bootstrap/app.php')
        ->handleRequest(Request::capture());
} catch (\Exception $e) {
    // If Laravel fails to bootstrap, likely due to missing APP_KEY or database issues
    // Check if we're already on the install page to avoid redirect loops
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($requestUri, '/install') === false) {
        // Redirect to install page
        header('Location: /install');
        exit;
    } else {
        // If we're already on install page but still getting errors, show the error
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Installation Error</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
                .error-box { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <h1>🏛️ Council ERP - Installation Error</h1>
            <div class='error-box'>
                <strong>Bootstrap Error:</strong> " . htmlspecialchars($e->getMessage()) . "
            </div>
            <p>Please check your .env file configuration and ensure all required extensions are installed.</p>
        </body>
        </html>";
        exit;
    }
}



// Check for common installation issues
$installationIssues = checkInstallationRequirements();
if (!empty($installationIssues)) {
    handlePreInstallation($installationIssues);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Catch Laravel bootstrap errors and redirect to install
try {
    // Bootstrap Laravel and handle the request...
    (require_once __DIR__.'/../bootstrap/app.php')
        ->handleRequest(Request::capture());
} catch (\Exception $e) {
    // If Laravel fails to bootstrap, likely due to missing APP_KEY or database issues
    // Check if we're already on the install page to avoid redirect loops
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($requestUri, '/install') === false) {
        // Redirect to install page
        header('Location: /install');
        exit;
    } else {
        // If we're already on install page but still getting errors, show the error
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Installation Error</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
                .error-box { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <h1>🏛️ Council ERP - Installation Error</h1>
            <div class='error-box'>
                <strong>Bootstrap Error:</strong> " . htmlspecialchars($e->getMessage()) . "
            </div>
            <p>Please check your .env file configuration and ensure all required extensions are installed.</p>
        </body>
        </html>";
        exit;
    }
}