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
            return redirect('/dashboard')->with('message', 'System is already installed.');
        }

        // Ensure basic environment is set up
        $this->ensureBasicEnvironment();
        $this->ensureStorageDirectories();

        // Get system requirements check
        $requirements = $this->checkSystemRequirements();
        $permissions = $this->checkPermissions();

        return view('install.index', compact('requirements', 'permissions'));
    }

    public function store(Request $request)
    {
        try {
            // Ensure storage directories exist with proper permissions
            $this->ensureStorageDirectories();

            // Validate all data
            $validated = $request->validate([
                'site_name' => 'required|string|max:255',
                'site_description' => 'nullable|string',
                'db_host' => 'required|string',
                'db_port' => 'required|numeric',
                'db_database' => 'required|string',
                'db_username' => 'required|string',
                'db_password' => 'nullable|string',
                'admin_name' => 'required|string|max:255',
                'admin_email' => 'required|email',
                'admin_password' => 'required|min:8|confirmed',
                'council_name' => 'required|string|max:255',
                'council_address' => 'required|string',
                'council_contact' => 'required|string',
            ]);

            // Test database connection first
            $this->testDatabaseConnection($request);

            // Update environment file with new database settings
            $this->updateEnvironmentFile($request);

            // Clear configuration cache
            try {
                Artisan::call('config:clear');
                Artisan::call('cache:clear');
            } catch (\Exception $e) {
                \Log::warning('Cache clear failed during installation: ' . $e->getMessage());
            }

            // Force reload the configuration
            $app = app();
            $app->make('config')->set('database.connections.mysql.host', $request->db_host);
            $app->make('config')->set('database.connections.mysql.port', $request->db_port);
            $app->make('config')->set('database.connections.mysql.database', $request->db_database);
            $app->make('config')->set('database.connections.mysql.username', $request->db_username);
            $app->make('config')->set('database.connections.mysql.password', $request->db_password);
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

            // Begin transaction for final setup
            DB::beginTransaction();

            try {
                // Create admin user
                $admin = User::create([
                    'name' => $request->admin_name,
                    'email' => $request->admin_email,
                    'password' => Hash::make($request->admin_password),
                    'role' => 'super_admin',
                    'is_active' => true,
                ]);

                \Log::info('Admin user created: ' . $admin->email);

                // Create council
                $council = Council::create([
                    'name' => $request->council_name,
                    'code' => strtoupper(substr($request->council_name, 0, 6)),
                    'description' => 'Main council office',
                    'address' => $request->council_address,
                    'phone' => $request->council_contact,
                    'email' => 'info@council.local',
                    'website' => null,
                    'is_active' => true,
                ]);

                \Log::info('Council record created: ' . $council->name);

                // Commit the transaction
                DB::commit();

                // Mark installation as complete
                $this->markInstallationComplete();

                \Log::info('Installation completed successfully');

                return redirect()->route('install.complete')
                    ->with('success', 'Installation completed successfully!')
                    ->with('admin_email', $admin->email);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Installation failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return back()->withErrors(['error' => 'Installation failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function testDatabase(Request $request)
    {
        try {
            $this->testDatabaseConnection($request);
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

    public function complete()
    {
        if (!$this->isInstalled()) {
            return redirect('/install');
        }

        $admin = User::where('role', 'super_admin')->first();

        if (!$admin) {
            return redirect('/install')->withErrors(['error' => 'Admin user not found']);
        }

        return view('install.complete', compact('admin'));
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
        $installData = [
            'completed_at' => now()->toISOString(),
            'version' => '1.0.0',
            'installation_id' => Str::uuid(),
            'domain' => request()->getHost()
        ];

        file_put_contents(storage_path('app/installed.lock'), json_encode($installData, JSON_PRETTY_PRINT));
    }

    private function ensureBasicEnvironment()
    {
        $envFile = base_path('.env');

        if (!file_exists($envFile)) {
            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), $envFile);
            } else {
                $envContent = "APP_NAME=\"Council ERP\"\n";
                $envContent .= "APP_ENV=production\n";
                $envContent .= "APP_DEBUG=false\n";
                $envContent .= "APP_KEY=\n";
                $envContent .= "APP_URL=https://game.rhinotap.net\n\n";
                $envContent .= "DB_CONNECTION=mysql\n";
                $envContent .= "DB_HOST=127.0.0.1\n";
                $envContent .= "DB_PORT=3306\n";
                $envContent .= "DB_DATABASE=council_erp\n";
                $envContent .= "DB_USERNAME=root\n";
                $envContent .= "DB_PASSWORD=\n\n";
                $envContent .= "SESSION_DRIVER=file\n";
                $envContent .= "SESSION_LIFETIME=120\n";

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
                @chmod($fullPath, 0755);
            }
        }
    }

    private function updateEnvironmentFile($request)
    {
        $envFile = base_path('.env');
        $this->ensureBasicEnvironment();

        $env = file_get_contents($envFile);

        // Update app settings
        $env = preg_replace('/^APP_NAME=.*$/m', 'APP_NAME="' . $request->site_name . '"', $env);
        $env = preg_replace('/^APP_URL=.*$/m', 'APP_URL=https://game.rhinotap.net', $env);

        // Update database settings
        $env = preg_replace('/^DB_HOST=.*$/m', 'DB_HOST=' . $request->db_host, $env);
        $env = preg_replace('/^DB_PORT=.*$/m', 'DB_PORT=' . $request->db_port, $env);
        $env = preg_replace('/^DB_DATABASE=.*$/m', 'DB_DATABASE=' . $request->db_database, $env);
        $env = preg_replace('/^DB_USERNAME=.*$/m', 'DB_USERNAME=' . $request->db_username, $env);
        $env = preg_replace('/^DB_PASSWORD=.*$/m', 'DB_PASSWORD=' . ($request->db_password ?: ''), $env);

        file_put_contents($envFile, $env);
    }

    private function checkSystemRequirements()
    {
        $requirements = [];

        $requirements[] = [
            'name' => 'PHP Version >= 8.1',
            'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'current' => PHP_VERSION
        ];

        $extensions = ['openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath'];
        foreach ($extensions as $extension) {
            $requirements[] = [
                'name' => 'PHP Extension: ' . $extension,
                'status' => extension_loaded($extension),
                'current' => extension_loaded($extension) ? 'Loaded' : 'Missing'
            ];
        }

        return $requirements;
    }

    private function checkPermissions()
    {
        $permissions = [];
        $paths = [
            'storage/framework/',
            'storage/logs/',
            'bootstrap/cache/',
        ];

        foreach ($paths as $path) {
            $fullPath = base_path($path);
            $permissions[] = [
                'name' => $path,
                'status' => is_writable($fullPath)
            ];
        }

        return $permissions;
    }
}