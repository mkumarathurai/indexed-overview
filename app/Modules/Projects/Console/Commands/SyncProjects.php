<?php

namespace App\Modules\Projects\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Projects\Services\ProjectsApiService;

class SyncProjects extends Command
{
    protected $signature = 'projects:sync {--clear-cache}';
    protected $description = 'Synchronize project data with Jira';

    public function handle(ProjectsApiService $projectsApiService)
    {
        if ($this->option('clear-cache')) {
            $this->call('cache:clear');
            $this->info('Cache cleared successfully.');
        }

        $this->info('Fetching projects from Jira...');
        $projects = $projectsApiService->getProjects(true); // Include archived projects

        foreach ($projects['values'] as $project) {
            $this->info("Syncing project: {$project['key']}");

            // Fetch issues for the project
            $issues = $projectsApiService->getIssues($project['key'], true); // Include archived issues

            // Process and store project and issue data
            $this->processProjectData($project, $issues);
        }

        $this->info('Projects sync completed.');
    }

    private function processProjectData($project, $issues)
    {
        // ...logic to store project and issue data in the database...
    }
}