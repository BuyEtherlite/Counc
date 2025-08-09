<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Council;

class InstallController extends Controller
{
    public function index()
    {
        // Check if system is already installed
        if ($this->isInstalled()) {
            return redirect('/')->with('message', 'System is already installed.');
        }

        // Redirect to step 1
        return redirect()->route('install.step1');
    }

    public function step1()
    {
        // Check if system is already installed
        if ($this->isInstalled()) {
            return redirect('/')->with('message', 'System is already installed.');
        }

        // Ensure basic environment is set up
        $this->ensureBasicEnvironment();

        // Get system requirements check
        $requirements = $this->checkSystemRequirements();
        $permissions = $this->checkPermissions();

        return view('install.step1', compact('requirements', 'permissions'));
    }

    public function step2()
    {
        // Check if system is already installed
        if ($this->isInstalled()) {
            return redirect('/')->with('message', 'System is already installed.');
        }

        return view('install.step2');
    }

    public function storeStep2(Request $request)
    {
        // Ensure storage directories exist with proper permissions
        $this->ensureStorageDirectories();
        
        // Validate step 2 data
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            // Test database connection first
            $this->testDatabaseConnection($request);

            // Update environment file with new database settings
            $this->updateEnvironmentFile($request);

            // Clear all config and cache (with error handling)
            try {
                Artisan::call('config:clear');
                Artisan::call('cache:clear');
            } catch (\Exception $e) {
                \Log::warning('Cache/config clear failed during installation: ' . $e->getMessage());
            }

            // Force reload the configuration
            $app = app();
            $app->make('config')->set('database.connections.mysql.host', $request->db_host);
            $app->make('config')->set('database.connections.mysql.port', $request->db_port);
            $app->make('config')->set('database.connections.mysql.database', $request->db_database);
            $app->make('config')->set('database.connections.mysql.username', $request->db_username);
            $app->make('config')->set('database.connections.mysql.password', $request->db_password);

            // Set default connection to mysql
            $app->make('config')->set('database.default', 'mysql');

            // Purge and reconnect to database
            DB::purge('mysql');
            DB::reconnect('mysql');

            // Test the connection again after reconnection
            try {
                DB::connection('mysql')->getPdo();
                \Log::info('Database connection successful');
            } catch (\Exception $e) {
                throw new \Exception('Failed to connect to database after configuration: ' . $e->getMessage());
            }

            // Run migrations with force flag
            \Log::info('Starting database migrations...');
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
                '--database' => 'mysql'
            ]);

            if ($exitCode !== 0) {
                $output = Artisan::output();
                \Log::error('Migration output: ' . $output);
                throw new \Exception('Migration failed: ' . $output);
            }

            \Log::info('Database migrations completed successfully');

            // Store step 2 data in session and also in a temporary file for persistence
            $sessionData = [
                'site_name' => $validated['site_name'],
                'site_description' => $validated['site_description'] ?? '',
                'completed_at' => now()->timestamp,
                'db_host' => $validated['db_host'],
                'db_port' => $validated['db_port'],
                'db_database' => $validated['db_database'],
                'db_username' => $validated['db_username']
            ];

            // Store in session
            session(['install_step2_data' => $sessionData]);
            session(['install_db_configured' => true]);
            session(['install_progress' => 'step2_completed']);
            
            // Also store in a temporary file for fallback
            $tempFile = storage_path('app/install_progress.json');
            file_put_contents($tempFile, json_encode($sessionData));

            \Log::info('Session data stored successfully: ' . json_encode($sessionData));
            \Log::info('Redirecting to step 3...');

            return redirect()->route('install.step3')
                ->with('success', 'Database configured and migrated successfully! Please complete the final step.')
                ->with('step2_data', $sessionData);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed: ' . json_encode($e->errors()));
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Step 2 installation failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Database configuration failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function step3()
    {
        // Check if system is already installed
        if ($this->isInstalled()) {
            return redirect('/')->with('message', 'System is already installed.');
        }

        // Ensure storage directories exist
        $this->ensureStorageDirectories();

        // Check if step 2 was completed - check session and fallback file
        $step2Data = session('install_step2_data');
        $dbConfigured = session('install_db_configured');
        
        // Fallback to temp file if session data is missing
        $tempFile = storage_path('app/install_progress.json');
        if (!$step2Data && file_exists($tempFile)) {
            $fileData = json_decode(file_get_contents($tempFile), true);
            if ($fileData) {
                $step2Data = $fileData;
                session(['install_step2_data' => $step2Data]);
                session(['install_db_configured' => true]);
                $dbConfigured = true;
            }
        }

        \Log::info('Step 3 session check:', [
            'step2_data' => $step2Data ? 'exists' : 'missing',
            'db_configured' => $dbConfigured,
            'session_id' => session()->getId()
        ]);

        if (!$step2Data && !$dbConfigured) {
            \Log::warning('Step 2 not completed, redirecting back');
            return redirect()->route('install.step2')
                ->withErrors(['error' => 'Please complete database configuration first.']);
        }

        // Verify database connection is still working
        try {
            DB::connection('mysql')->getPdo();
            \Log::info('Database connection verified for step 3');
        } catch (\Exception $e) {
            \Log::error('Database connection lost in step 3: ' . $e->getMessage());
            session()->forget(['install_step2_data', 'install_db_configured', 'install_progress']);
            return redirect()->route('install.step2')
                ->withErrors(['error' => 'Database connection lost. Please reconfigure database settings.']);
        }

        return view('install.step3');
    }

    public function completeInstallation(Request $request)
    {
        try {
            // Start session explicitly if not started
            if (!session()->isStarted()) {
                session()->start();
            }

            // Check if step 2 was completed and database is configured
            if (!session('install_step2_data') || !session('install_db_configured')) {
                return redirect()->route('install.step2')->withErrors(['error' => 'Please complete database configuration first.']);
            }

            $request->validate([
                'admin_name' => 'required|string|max:255',
                'admin_email' => 'required|email',
                'admin_password' => 'required|min:8|confirmed',
                'council_name' => 'required|string|max:255',
                'council_address' => 'required|string',
                'council_contact' => 'required|string',
            ]);

            // Verify database connection before proceeding
            try {
                DB::connection('mysql')->getPdo();
            } catch (\Exception $e) {
                session()->forget(['install_step2_data', 'install_db_configured']);
                return redirect()->route('install.step2')->withErrors(['error' => 'Database connection lost. Please reconfigure database settings.']);
            }

            // Create admin user
            $admin = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'super_admin',
                'is_active' => true,
            ]);

            \Log::info('Admin user created: ' . $admin->email);

            // Store admin password temporarily for display (security: only in session)
            session(['temp_admin_password' => $request->admin_password]);

            // Create council record
            $council = Council::create([
                'name' => $request->council_name,
                'address' => $request->council_address,
                'contact_info' => $request->council_contact,
                'is_primary' => true,
            ]);

            \Log::info('Council record created: ' . $council->name);

            // Mark installation as complete
            $this->markInstallationComplete();

            // Clear installation session data and temp files
            session()->forget(['install_step2_data', 'install_db_configured', 'install_progress']);
            
            // Remove temporary installation files
            $tempFile = storage_path('app/install_progress.json');
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }

            \Log::info('Installation completed successfully');

            return redirect()->route('install.complete.view')->with('success', 'Installation completed successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Installation failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Installation failed: ' . $e->getMessage()])->withInput();
        }
    }

    private function testDatabaseConnection($request)
    {
        try {
            // First test if we can connect to MySQL server
            $dsn = "mysql:host={$request->db_host};port={$request->db_port}";
            $testConnection = new \PDO($dsn, $request->db_username, $request->db_password);
            $testConnection = null;

            // Then test if database exists, create if not
            $connection = [
                'driver' => 'mysql',
                'host' => $request->db_host,
                'port' => $request->db_port,
                'database' => $request->db_database,
                'username' => $request->db_username,
                'password' => $request->db_password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
                'options' => [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ],
            ];

            config(['database.connections.test_connection' => $connection]);
            DB::connection('test_connection')->getPdo();
            
            // Try to create database if it doesn't exist
            try {
                DB::connection('test_connection')->statement("CREATE DATABASE IF NOT EXISTS `{$request->db_database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            } catch (\Exception $e) {
                // Database might already exist, that's okay
                \Log::info('Database creation attempted: ' . $e->getMessage());
            }
            
            DB::purge('test_connection');
        } catch (\Exception $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    private function isInstalled()
    {
        return file_exists(storage_path('app/installed.lock'));
    }

    private function markInstallationComplete()
    {
        file_put_contents(storage_path('app/installed.lock'), now());
    }

    public function complete()
    {
        // This method handles GET requests to /install/complete
        // It should only be accessible after installation is complete
        if (!$this->isInstalled()) {
            return redirect('/install');
        }

        // Get admin user (assuming it's the first user created)
        $admin = \App\Models\User::where('role', 'super_admin')->first();

        if (!$admin) {
            return redirect('/install')->withErrors(['error' => 'Admin user not found']);
        }

        return view('install.complete', compact('admin'));
    }

    private function ensureBasicEnvironment()
    {
        $envFile = base_path('.env');

        // If .env doesn't exist, create it from .env.example
        if (!file_exists($envFile)) {
            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), $envFile);
            } else {
                // Create a minimal .env file
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

                file_put_contents($envFile, $envContent);
            }
        }

        // Generate APP_KEY if it's missing
        $env = file_get_contents($envFile);
        if (preg_match('/^APP_KEY=$/m', $env) || preg_match('/^APP_KEY=\s*$/m', $env)) {
            $key = 'base64:' . base64_encode(random_bytes(32));
            $env = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $env);
            file_put_contents($envFile, $env);
        }
    }

    private function ensureStorageDirectories()
    {
        $directories = [
            'storage/framework/sessions',
            'storage/framework/cache/data',
            'storage/framework/views',
            'storage/app/public',
            'storage/logs',
            'bootstrap/cache'
        ];

        foreach ($directories as $dir) {
            $fullPath = base_path($dir);
            if (!is_dir($fullPath)) {
                if (!@mkdir($fullPath, 0755, true)) {
                    \Log::warning("Failed to create directory: {$fullPath}");
                } else {
                    @chmod($fullPath, 0755);
                }
            } else {
                // Ensure proper permissions
                @chmod($fullPath, 0755);
            }
        }

        // Ensure bootstrap/cache has proper permissions
        $bootstrapCache = base_path('bootstrap/cache');
        if (is_dir($bootstrapCache)) {
            @chmod($bootstrapCache, 0755);
        }
    }

    private function updateEnvironmentFile($request)
    {
        $envFile = base_path('.env');

        // Ensure .env exists
        $this->ensureBasicEnvironment();

        $env = file_get_contents($envFile);

        // Update app settings
        $env = preg_replace('/^APP_NAME=.*$/m', 'APP_NAME="' . $request->site_name . '"', $env);

        // Ensure we're using MySQL connection
        $env = preg_replace('/^DB_CONNECTION=.*$/m', 'DB_CONNECTION=mysql', $env);

        // Update database settings
        $env = preg_replace('/^DB_HOST=.*$/m', 'DB_HOST=' . $request->db_host, $env);
        $env = preg_replace('/^DB_PORT=.*$/m', 'DB_PORT=' . $request->db_port, $env);
        $env = preg_replace('/^DB_DATABASE=.*$/m', 'DB_DATABASE=' . $request->db_database, $env);
        $env = preg_replace('/^DB_USERNAME=.*$/m', 'DB_USERNAME=' . $request->db_username, $env);
        $env = preg_replace('/^DB_PASSWORD=.*$/m', 'DB_PASSWORD="' . $request->db_password . '"', $env);

        // Add DB_FOREIGN_KEYS if not present
        if (!preg_match('/^DB_FOREIGN_KEYS=/m', $env)) {
            $env .= "\nDB_FOREIGN_KEYS=true\n";
        } else {
            $env = preg_replace('/^DB_FOREIGN_KEYS=.*$/m', 'DB_FOREIGN_KEYS=true', $env);
        }

        // Write the updated environment file
        if (!file_put_contents($envFile, $env)) {
            throw new \Exception('Failed to update .env file');
        }

        \Log::info('Environment file updated successfully');
    }

    // Test database connection via AJAX
    public function testDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            // Create a temporary database connection
            $connection = [
                'driver' => 'mysql',
                'host' => $request->db_host,
                'port' => $request->db_port,
                'database' => $request->db_database,
                'username' => $request->db_username,
                'password' => $request->db_password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ];

            // Set the test connection
            config(['database.connections.test_connection' => $connection]);

            // Test the connection
            DB::connection('test_connection')->getPdo();

            return response()->json([
                'success' => true,
                'message' => 'Database connection successful!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ]);
        }
    }

    // Check PHP requirements
    private function checkSystemRequirements()
    {
        $requirements = [
            'php_version' => [
                'name' => 'PHP Version (>= 8.2)',
                'status' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'current' => PHP_VERSION
            ],
            'composer_installed' => [
                'name' => 'Composer Dependencies',
                'status' => file_exists(base_path('vendor/autoload.php')),
                'current' => file_exists(base_path('vendor/autoload.php')) ? 'Installed' : 'Missing - Run composer install'
            ],
            'env_file' => [
                'name' => 'Environment File (.env)',
                'status' => file_exists(base_path('.env')),
                'current' => file_exists(base_path('.env')) ? 'Present' : 'Missing - Will be created'
            ],
            'pdo' => [
                'name' => 'PDO Extension',
                'status' => extension_loaded('pdo'),
                'current' => extension_loaded('pdo') ? 'Enabled' : 'Disabled'
            ],
            'pdo_mysql' => [
                'name' => 'PDO MySQL Extension',
                'status' => extension_loaded('pdo_mysql'),
                'current' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled'
            ],
            'mbstring' => [
                'name' => 'Mbstring Extension',
                'status' => extension_loaded('mbstring'),
                'current' => extension_loaded('mbstring') ? 'Enabled' : 'Disabled'
            ],
            'openssl' => [
                'name' => 'OpenSSL Extension',
                'status' => extension_loaded('openssl'),
                'current' => extension_loaded('openssl') ? 'Enabled' : 'Disabled'
            ],
            'tokenizer' => [
                'name' => 'Tokenizer Extension',
                'status' => extension_loaded('tokenizer'),
                'current' => extension_loaded('tokenizer') ? 'Enabled' : 'Disabled'
            ],
            'xml' => [
                'name' => 'XML Extension',
                'status' => extension_loaded('xml'),
                'current' => extension_loaded('xml') ? 'Enabled' : 'Disabled'
            ],
            'ctype' => [
                'name' => 'Ctype Extension',
                'status' => extension_loaded('ctype'),
                'current' => extension_loaded('ctype') ? 'Enabled' : 'Disabled'
            ],
            'json' => [
                'name' => 'JSON Extension',
                'status' => extension_loaded('json'),
                'current' => extension_loaded('json') ? 'Enabled' : 'Disabled'
            ],
            'curl' => [
                'name' => 'cURL Extension',
                'status' => extension_loaded('curl'),
                'current' => extension_loaded('curl') ? 'Enabled' : 'Disabled'
            ]
        ];

        return $requirements;
    }

    // Check folder permissions
    private function checkPermissions()
    {
        $paths = [
            'storage/app' => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $permissions = [];

        foreach ($paths as $name => $path) {
            $permissions[$name] = [
                'name' => $name,
                'status' => File::isWritable($path),
                'path' => $path
            ];
        }

        return $permissions;
    }
}