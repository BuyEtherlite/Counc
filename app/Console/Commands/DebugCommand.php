<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DebugAgent;

class DebugCommand extends Command
{
    protected $signature = 'debug:status';
    protected $description = 'Display system debug information';

    public function handle(DebugAgent $debugAgent)
    {
        $report = $debugAgent->generateReport();

        $this->info('=== System Debug Report ===');
        $this->line('Installation Status: ' . ($report['installation']['is_installed'] ? 'Installed' : 'Not Installed'));
        $this->line('Database Status: ' . ($report['database']['connected'] ? 'Connected' : 'Disconnected'));
        $this->line('Environment: ' . config('app.env'));
        $this->line('Debug Mode: ' . (config('app.debug') ? 'Enabled' : 'Disabled'));

        if (!empty($report['errors'])) {
            $this->error('Errors found:');
            foreach ($report['errors'] as $error) {
                $this->line('- ' . $error);
            }
        }

        return 0;
    }
}