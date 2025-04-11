<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Projects\Services\ProjectsApiService;
use Carbon\Carbon;

class SyncProjectHours extends Command
{
    protected $signature = 'projects:sync-hours {period? : The period to sync (YYYY-MM)}';
    protected $description = 'Sync project hours from Jira for a specific period';

    public function handle(ProjectsApiService $projectsApi)
    {
        $period = $this->argument('period') ?? Carbon::now()->format('Y-m');

        $this->info('Starting project hours sync for ' . $period);
        $projectsApi->refreshProjectHours($period);
        $this->info('Project hours sync completed for ' . $period);
    }
}
