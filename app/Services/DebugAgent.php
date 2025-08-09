
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DebugAgent
{
    private array $debugData = [];
    private bool $enabled = true;

    public function __construct()
    {
        $this->enabled = config('app.debug', false);
    }

    public function enable(): self
    {
        $this->enabled = true;
        return $this;
    }

    public function disable(): self
    {
        $this->enabled = false;
        return $this;
    }

    public function log(string $message, array $context = [], string $level = 'info'): self
    {
        if (!$this->enabled) {
            return $this;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = $trace[1] ?? [];
        
        $logData = [
            'message' => $message,
            'context' => $context,
            'timestamp' => now()->toISOString(),
            'caller' => [
                'file' => $caller['file'] ?? 'unknown',
                'line' => $caller['line'] ?? 0,
                'function' => $caller['function'] ?? 'unknown',
                'class' => $caller['class'] ?? null,
            ],
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ];

        $this->debugData[] = $logData;

        // Log to Laravel's logging system
        Log::{$level}($message, array_merge($context, ['debug_agent' => true]));

        return $this;
    }

    public function checkDatabase(): array
    {
        $results = [
            'connection' => false,
            'tables' => [],
            'migrations' => [],
            'error' => null
        ];

        try {
            DB::connection()->getPdo();
            $results['connection'] = true;

            // Get table list
            $tables = DB::select('SHOW TABLES');
            $results['tables'] = array_map(function($table) {
                return array_values((array)$table)[0];
            }, $tables);

            // Check migrations
            if (in_array('migrations', $results['tables'])) {
                $results['migrations'] = DB::table('migrations')
                    ->select('migration', 'batch')
                    ->orderBy('batch')
                    ->get()
                    ->toArray();
            }

        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
            $this->log('Database check failed', ['error' => $e->getMessage()], 'error');
        }

        return $results;
    }

    public function checkInstallation(): array
    {
        $checks = [
            'env_exists' => file_exists(base_path('.env')),
            'app_key_set' => !empty(config('app.key')),
            'storage_writable' => is_writable(storage_path()),
            'cache_writable' => is_writable(storage_path('framework/cache')),
            'installed_lock' => file_exists(storage_path('app/installed.lock')),
            'vendor_exists' => is_dir(base_path('vendor')),
            'database_ready' => file_exists(storage_path('app/database_ready.lock')),
        ];

        $this->log('Installation check completed', $checks);
        return $checks;
    }

    public function testRoute(string $route, array $parameters = []): array
    {
        try {
            $url = route($route, $parameters);
            $this->log('Testing route', ['route' => $route, 'url' => $url, 'parameters' => $parameters]);
            
            return [
                'route' => $route,
                'url' => $url,
                'parameters' => $parameters,
                'exists' => true
            ];
        } catch (\Exception $e) {
            $this->log('Route test failed', ['route' => $route, 'error' => $e->getMessage()], 'error');
            
            return [
                'route' => $route,
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function checkPermissions(): array
    {
        $paths = [
            'storage' => storage_path(),
            'storage/app' => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $results = [];
        foreach ($paths as $name => $path) {
            $results[$name] = [
                'path' => $path,
                'exists' => file_exists($path),
                'readable' => is_readable($path),
                'writable' => is_writable($path),
                'permissions' => file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : null,
            ];
        }

        $this->log('Permission check completed', $results);
        return $results;
    }

    public function generateReport(): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_enabled' => config('app.debug'),
            'installation_status' => $this->checkInstallation(),
            'database_status' => $this->checkDatabase(),
            'permissions' => $this->checkPermissions(),
            'debug_logs' => $this->debugData,
            'memory_usage' => [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit'),
            ],
        ];
    }

    public function clearLogs(): self
    {
        $this->debugData = [];
        return $this;
    }

    public function saveReport(string $filename = null): string
    {
        $filename = $filename ?: 'debug_report_' . now()->format('Y_m_d_H_i_s') . '.json';
        $path = storage_path('logs/' . $filename);
        
        $report = $this->generateReport();
        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT));
        
        $this->log('Debug report saved', ['path' => $path]);
        return $path;
    }
}
