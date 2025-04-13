<?php

namespace App\Modules\Employees\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Employees\Services\JiraApiService;
use App\Modules\Employees\Models\Employee;
use Illuminate\Support\Facades\Log;

class SyncEmployees extends Command
{
    protected $signature = 'employees:sync';
    protected $description = 'Sync employees from Jira';

    protected $jiraApiService;

    public function __construct(JiraApiService $jiraApiService)
    {
        parent::__construct();
        $this->jiraApiService = $jiraApiService;
    }

    public function handle()
    {
        $this->info('Starting employee sync from Jira...');

        try {
            $jiraUsers = $this->jiraApiService->getUsers();
            
            $syncCount = 0;
            foreach ($jiraUsers as $user) {
                $details = $this->jiraApiService->getUserDetails($user['accountId']);
                
                Employee::updateOrCreate(
                    ['jira_account_id' => $user['accountId']],
                    [
                        'name' => $user['displayName'],
                        'email' => $user['emailAddress'] ?? null,
                        'title' => $details['title'] ?? null,
                        'type' => 'internal',
                        'status' => $user['active'] ?? true,
                    ]
                );
                $syncCount++;
            }

            $this->info("Successfully synced {$syncCount} employees");
        } catch (\Exception $e) {
            Log::error('Employee sync failed', ['error' => $e->getMessage()]);
            $this->error('Sync failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
