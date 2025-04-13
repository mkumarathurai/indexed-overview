<?php

namespace App\Modules\Employees\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ListJiraGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jira:list-groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all groups from Jira';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching Jira groups...');
        
        try {
            $baseUrl = config('services.jira.base_url');
            $apiToken = config('services.jira.api_token');
            $email = config('services.jira.email');
            
            $this->info("Base URL: $baseUrl");
            $this->info("Email: $email");
            
            // Fetch groups from Jira
            $response = Http::withBasicAuth($email, $apiToken)
                ->get("{$baseUrl}/rest/api/3/groups");
            
            if ($response->successful()) {
                $data = $response->json();
                $groups = $data['groups'] ?? [];
                
                $this->info('Found ' . count($groups) . ' groups in Jira:');
                
                $rows = [];
                foreach ($groups as $index => $group) {
                    $rows[] = [
                        $index + 1,
                        $group['name'] ?? 'N/A',
                        $group['groupId'] ?? 'N/A'
                    ];
                }
                
                $this->table(['#', 'Group Name', 'Group ID'], $rows);
                
                return Command::SUCCESS;
            } else {
                $this->error('Error fetching groups from Jira API: ' . $response->body());
                Log::error('Error fetching groups from Jira API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Error fetching groups from Jira: ' . $e->getMessage());
            Log::error('Error fetching groups from Jira', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
} 