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

        // Ensure basic environment is set up
        $this->ensureBasicEnvironment();

        // Get system requirements check
        $requirements = $this->checkSystemRequirements();
        $permissions = $this->checkPermissions();

        return view('install.index', compact('requirements', 'permissions'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'site_name' => 'required|string|max:255',
                'site_description' => 'nullable|string',
                'admin_name' => 'required|string|max:255',
                'admin_email' => 'required|email',
                'admin_password' => 'required|min:8|confirmed',
                'db_host' => 'required|string',
                'db_port' => 'required|numeric',
                'db_database' => 'required|string',
                'db_username' => 'required|string',
                'db_password' => 'nullable|string',
                'council_name' => 'required|string|max:255',
                'council_address' => 'required|string',
                'council_contact' => 'required|string',
            ]);

            // First, test database connection before proceeding
            $this->testDatabaseConnection($request);

            // Update environment file
            $this->updateEnvironmentFile($request);

            // Clear config cache to ensure new environment variables are loaded
            Artisan::call('config:clear');

            // Set database connection for this request
            config(['database.connections.mysql.host' => $request->db_host]);
            config(['database.connections.mysql.port' => $request->db_port]);
            config(['database.connections.mysql.database' => $request->db_database]);
            config(['database.connections.mysql.username' => $request->db_username]);
            config(['database.connections.mysql.password' => $request->db_password]);

            // Reconnect to database with new settings
            DB::purge('mysql');
            DB::reconnect('mysql');

            // Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // Create admin user
            $admin = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'super_admin',
                'is_active' => true,
            ]);

            // Store admin password temporarily for display (security: only in session)
            session(['temp_admin_password' => $request->admin_password]);

            // Create council record
            Council::create([
                'name' => $request->council_name,
                'address' => $request->council_address,
                'contact_info' => $request->council_contact,
                'is_primary' => true,
            ]);

            // Mark installation as complete
            $this->markInstallationComplete();

            return redirect()->route('install.complete')->with('success', 'Installation completed successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Installation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Installation failed: ' . $e->getMessage()])->withInput();
        }
    }

    private function testDatabaseConnection($request)
    {
        try {
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

            config(['database.connections.test_connection' => $connection]);
            DB::connection('test_connection')->getPdo();
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

    private function updateEnvironmentFile($request)
    {
        $envFile = base_path('.env');
        
        // Ensure .env exists
        $this->ensureBasicEnvironment();
        
        $env = file_get_contents($envFile);

        $env = preg_replace('/^APP_NAME=.*$/m', 'APP_NAME="' . $request->site_name . '"', $env);
        $env = preg_replace('/^DB_HOST=.*$/m', 'DB_HOST=' . $request->db_host, $env);
        $env = preg_replace('/^DB_PORT=.*$/m', 'DB_PORT=' . $request->db_port, $env);
        $env = preg_replace('/^DB_DATABASE=.*$/m', 'DB_DATABASE=' . $request->db_database, $env);
        $env = preg_replace('/^DB_USERNAME=.*$/m', 'DB_USERNAME=' . $request->db_username, $env);
        $env = preg_replace('/^DB_PASSWORD=.*$/m', 'DB_PASSWORD=' . $request->db_password, $env);

        file_put_contents($envFile, $env);
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