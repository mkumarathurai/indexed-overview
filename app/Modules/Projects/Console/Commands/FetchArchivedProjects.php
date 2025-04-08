<?php

namespace App\Modules\Projects\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Projects\Services\ProjectsApiService;

class FetchArchivedProjects extends Command
{
    protected $signature = 'jira:fetch-archived';
    protected $description = 'Fetch archived projects and tasks from Jira';

    public function handle(ProjectsApiService $projectsApiService)
    {
        $this->info('Fetching archived projects from Jira...');
        $projects = $projectsApiService->getProjects(true);

        foreach ($projects['values'] as $project) {
            if ($project['archived'] ?? false) {
                $this->info("Processing archived project: {$project['key']}");
                $issues = $projectsApiService->getIssues($project['key'], true);
                $this->processArchivedData($project, $issues);
            }
        }

        $this->info('Archived projects fetch completed.');
    }

    private function processArchivedData($project, $issues)
    {
        \DB::table('projects')->updateOrInsert(
            ['key' => $project['key']],
            [
                'name' => $project['name'],
                'is_archived' => true,
                'updated_at' => now()
            ]
        );
    }
}
