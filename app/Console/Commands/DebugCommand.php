
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DebugAgent;

class DebugCommand extends Command
{
    protected $signature = 'debug:agent 
                           {--report : Generate full debug report}
                           {--install : Check installation status}
                           {--database : Check database status}
                           {--permissions : Check file permissions}
                           {--save : Save report to file}';

    protected $description = 'Debug agent to help troubleshoot application issues';

    public function handle(DebugAgent $debugAgent)
    {
        $this->info('🔍 Council ERP Debug Agent');
        $this->line('');

        if ($this->option('install')) {
            $this->checkInstallation($debugAgent);
        } elseif ($this->option('database')) {
            $this->checkDatabase($debugAgent);
        } elseif ($this->option('permissions')) {
            $this->checkPermissions($debugAgent);
        } elseif ($this->option('report')) {
            $this->generateReport($debugAgent);
        } else {
            $this->showQuickStatus($debugAgent);
        }

        if ($this->option('save')) {
            $path = $debugAgent->saveReport();
            $this->info("📁 Report saved to: {$path}");
        }
    }

    private function showQuickStatus(DebugAgent $debugAgent)
    {
        $this->info('Quick Status Check:');
        $this->line('');

        // Installation check
        $install = $debugAgent->checkInstallation();
        $this->line('📦 Installation Status:');
        foreach ($install as $check => $status) {
            $icon = $status ? '✅' : '❌';
            $this->line("  {$icon} " . str_replace('_', ' ', ucfirst($check)));
        }

        $this->line('');

        // Database check
        $db = $debugAgent->checkDatabase();
        $this->line('🗄️ Database Status:');
        $icon = $db['connection'] ? '✅' : '❌';
        $this->line("  {$icon} Connection: " . ($db['connection'] ? 'Connected' : 'Failed'));
        
        if ($db['connection']) {
            $this->line("  📊 Tables: " . count($db['tables']));
            $this->line("  🔄 Migrations: " . count($db['migrations']));
        }

        if ($db['error']) {
            $this->error("  Error: " . $db['error']);
        }
    }

    private function checkInstallation(DebugAgent $debugAgent)
    {
        $this->info('📦 Installation Status Check:');
        $this->line('');

        $checks = $debugAgent->checkInstallation();
        
        foreach ($checks as $check => $status) {
            $icon = $status ? '✅' : '❌';
            $message = str_replace('_', ' ', ucfirst($check));
            $this->line("  {$icon} {$message}");
        }

        $allPassed = array_reduce($checks, fn($carry, $status) => $carry && $status, true);
        
        $this->line('');
        if ($allPassed) {
            $this->info('🎉 All installation checks passed!');
        } else {
            $this->warn('⚠️ Some installation checks failed. Please review the items above.');
        }
    }

    private function checkDatabase(DebugAgent $debugAgent)
    {
        $this->info('🗄️ Database Status Check:');
        $this->line('');

        $db = $debugAgent->checkDatabase();

        if ($db['connection']) {
            $this->info('✅ Database connection successful');
            $this->line('');
            
            $this->line('📊 Tables (' . count($db['tables']) . '):');
            foreach ($db['tables'] as $table) {
                $this->line("  - {$table}");
            }

            $this->line('');
            $this->line('🔄 Migrations (' . count($db['migrations']) . '):');
            foreach ($db['migrations'] as $migration) {
                $this->line("  - {$migration->migration} (batch: {$migration->batch})");
            }
        } else {
            $this->error('❌ Database connection failed');
            if ($db['error']) {
                $this->error("Error: " . $db['error']);
            }
        }
    }

    private function checkPermissions(DebugAgent $debugAgent)
    {
        $this->info('🔐 File Permissions Check:');
        $this->line('');

        $permissions = $debugAgent->checkPermissions();

        foreach ($permissions as $name => $info) {
            $this->line("📁 {$name}:");
            
            $existsIcon = $info['exists'] ? '✅' : '❌';
            $readableIcon = $info['readable'] ? '✅' : '❌';
            $writableIcon = $info['writable'] ? '✅' : '❌';
            
            $this->line("  {$existsIcon} Exists: " . ($info['exists'] ? 'Yes' : 'No'));
            $this->line("  {$readableIcon} Readable: " . ($info['readable'] ? 'Yes' : 'No'));
            $this->line("  {$writableIcon} Writable: " . ($info['writable'] ? 'Yes' : 'No'));
            
            if ($info['permissions']) {
                $this->line("  🔢 Permissions: " . $info['permissions']);
            }
            
            $this->line('');
        }
    }

    private function generateReport(DebugAgent $debugAgent)
    {
        $this->info('📋 Generating Full Debug Report...');
        $this->line('');

        $report = $debugAgent->generateReport();

        $this->info("🖥️ System Information:");
        $this->line("  PHP Version: " . $report['php_version']);
        $this->line("  Laravel Version: " . $report['laravel_version']);
        $this->line("  Environment: " . $report['environment']);
        $this->line("  Debug Mode: " . ($report['debug_enabled'] ? 'Enabled' : 'Disabled'));
        $this->line('');

        $this->info("💾 Memory Usage:");
        $this->line("  Current: " . number_format($report['memory_usage']['current'] / 1024 / 1024, 2) . " MB");
        $this->line("  Peak: " . number_format($report['memory_usage']['peak'] / 1024 / 1024, 2) . " MB");
        $this->line("  Limit: " . $report['memory_usage']['limit']);
        $this->line('');

        // Show summaries of other checks
        $installPassed = array_reduce($report['installation_status'], fn($c, $s) => $c && $s, true);
        $dbConnected = $report['database_status']['connection'];
        
        $this->info("📦 Installation: " . ($installPassed ? '✅ All checks passed' : '❌ Issues found'));
        $this->info("🗄️ Database: " . ($dbConnected ? '✅ Connected' : '❌ Connection failed'));
        
        $this->line('');
        $this->info('Use --install, --database, or --permissions for detailed breakdowns.');
    }
}
