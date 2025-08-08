<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
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

        return view('install.index');
    }

    public function store(Request $request)
    {
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

        try {
            // Update environment file
            $this->updateEnvironmentFile($request);

            // Test database connection
            config(['database.connections.mysql.host' => $request->db_host]);
            config(['database.connections.mysql.port' => $request->db_port]);
            config(['database.connections.mysql.database' => $request->db_database]);
            config(['database.connections.mysql.username' => $request->db_username]);
            config(['database.connections.mysql.password' => $request->db_password]);

            DB::connection()->getPdo();

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

            // Create council record
            Council::create([
                'name' => $request->council_name,
                'address' => $request->council_address,
                'contact_info' => $request->council_contact,
                'is_primary' => true,
            ]);

            // Mark installation as complete
            $this->markInstallationComplete();

            return view('install.complete', compact('admin'));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Installation failed: ' . $e->getMessage()]);
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

    private function updateEnvironmentFile($request)
    {
        $envFile = base_path('.env');
        $env = file_get_contents($envFile);

        $env = preg_replace('/^APP_NAME=.*$/m', 'APP_NAME="' . $request->site_name . '"', $env);
        $env = preg_replace('/^DB_HOST=.*$/m', 'DB_HOST=' . $request->db_host, $env);
        $env = preg_replace('/^DB_PORT=.*$/m', 'DB_PORT=' . $request->db_port, $env);
        $env = preg_replace('/^DB_DATABASE=.*$/m', 'DB_DATABASE=' . $request->db_database, $env);
        $env = preg_replace('/^DB_USERNAME=.*$/m', 'DB_USERNAME=' . $request->db_username, $env);
        $env = preg_replace('/^DB_PASSWORD=.*$/m', 'DB_PASSWORD=' . $request->db_password, $env);

        file_put_contents($envFile, $env);
    }
}