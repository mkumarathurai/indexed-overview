<?php

namespace App\Modules\Projects\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Projects\Services\ProjectsApiService;
use Carbon\Carbon;

class SyncProjectHours extends Command
{
    protected $signature = 'projects:sync-hours';
    protected $description = 'Sync project hours from Jira for the current month';

    protected $projectsApiService;

    public function __construct(ProjectsApiService $projectsApiService)
    {
        parent::__construct();
        $this->projectsApiService = $projectsApiService;
    }

    public function handle()
    {
        $currentPeriod = Carbon::now()->format('Y-m');
        $this->info("Starting sync for period: {$currentPeriod}");

        try {
            $this->projectsApiService->refreshProjectHours($currentPeriod);
            $this->info('Sync completed successfully');
        } catch (\Exception $e) {
            $this->error("Sync failed: {$e->getMessage()}");
            \Log::error("Project hours sync failed: {$e->getMessage()}");
        }
    }
}
