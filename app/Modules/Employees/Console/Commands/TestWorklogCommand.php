<?php

namespace App\Modules\Employees\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Employees\Services\JiraApiService;
use Illuminate\Support\Facades\Log;
use App\Modules\Employees\Services\TempoService;
use Illuminate\Support\Facades\Http;
use App\Modules\Employees\Models\Employee;
use App\Modules\Holidays\Models\HolidayWorklog;
use Illuminate\Support\Facades\DB;
use App\Modules\Holidays\Models\Holiday;

class TestWorklogCommand extends Command
{
    protected $signature = 'test:worklog';
    protected $description = 'Test fetching worklogs for INTERNAL-11';

    public function handle()
    {
        $epicKey = 'INTERNAL-9';
        $tempoService = app(TempoService::class);
        $jiraApiService = app(JiraApiService::class);

        $this->info("Fetching worklogs for epic: {$epicKey}");

        try {
            // First, ensure all employees have holiday records for the current year
            $employees = Employee::where('email', 'like', '%@indexed.dk')->get();
            foreach ($employees as $employee) {
                Holiday::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'year' => date('Y')
                    ],
                    [
                        'total_days' => 25,
                        'used_days' => 0,
                        'remaining_days' => 25,
                        'last_updated' => now()
                    ]
                );
            }

            // First get the epic details from Jira
            $this->info("\nFetching epic details from Jira...");
            $response = Http::withBasicAuth(
                config('services.jira.email'),
                config('services.jira.api_token')
            )->get(config('services.jira.base_url') . "/rest/api/3/issue/{$epicKey}", [
                'fields' => 'summary'
            ]);

            if (!$response->successful()) {
                $this->error("Failed to fetch epic details");
                $this->error("Status: " . $response->status());
                $this->error("Response: " . $response->body());
                return;
            }

            $epic = $response->json();
            $this->info("Epic found: " . ($epic['fields']['summary'] ?? 'No summary'));

            // Get all issues linked to this epic using JQL
            $this->info("\nFetching linked issues...");
            $response = Http::withBasicAuth(
                config('services.jira.email'),
                config('services.jira.api_token')
            )->get(config('services.jira.base_url') . "/rest/api/3/search", [
                'jql' => "parent = {$epicKey} OR 'Epic Link' = {$epicKey}",
                'fields' => 'key,summary'
            ]);

            if (!$response->successful()) {
                $this->error("Failed to fetch linked issues");
                $this->error("Status: " . $response->status());
                $this->error("Response: " . $response->body());
                return;
            }

            $data = $response->json();
            $issues = $data['issues'] ?? [];
            $this->info("Found " . count($issues) . " linked issues");

            // Get worklogs for the epic and all linked issues
            $allWorklogs = [];
            
            // Get epic worklogs
            $this->info("\nFetching epic worklogs from Tempo...");
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.tempo.api_token'),
                'Accept' => 'application/json'
            ])->get('https://api.eu.tempo.io/4/worklogs', [
                'issueId' => $epic['id']
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $allWorklogs = array_merge($allWorklogs, $data['results'] ?? []);
            }

            // Get worklogs for each linked issue
            foreach ($issues as $issue) {
                $this->info("\nFetching worklogs for issue: " . $issue['key']);
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('services.tempo.api_token'),
                    'Accept' => 'application/json'
                ])->get('https://api.eu.tempo.io/4/worklogs', [
                    'issueId' => $issue['id']
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $allWorklogs = array_merge($allWorklogs, $data['results'] ?? []);
                }
            }
            
            $this->info("\nFound " . count($allWorklogs) . " total worklogs");

            // Get all employees with @indexed.dk emails
            $employees = Employee::where('email', 'like', '%@indexed.dk')
                ->get()
                ->keyBy('email');

            $this->info("\nFound " . $employees->count() . " employees");

            DB::beginTransaction();
            try {
                // First, delete all existing worklogs for these employees
                $employeeIds = $employees->pluck('id')->toArray();
                HolidayWorklog::whereIn('employee_id', $employeeIds)->delete();
                
                $storedCount = 0;
                foreach ($allWorklogs as $worklog) {
                    $authorEmail = null;
                    
                    // Get author details from Jira
                    if (isset($worklog['author']['accountId'])) {
                        $response = Http::withBasicAuth(
                            config('services.jira.email'),
                            config('services.jira.api_token')
                        )->get(config('services.jira.base_url') . "/rest/api/3/user", [
                            'accountId' => $worklog['author']['accountId']
                        ]);

                        if ($response->successful()) {
                            $authorData = $response->json();
                            $authorEmail = $authorData['emailAddress'] ?? null;
                        } else {
                            $authorEmail = $worklog['author']['email'] ?? null;
                        }
                    }

                    if (!$authorEmail || !isset($employees[$authorEmail])) {
                        $this->info("Skipping worklog for unknown employee: " . ($authorEmail ?? 'Unknown'));
                        continue;
                    }

                    $employee = $employees[$authorEmail];
                    
                    try {
                        // Create worklog
                        HolidayWorklog::create([
                            'employee_id' => $employee->id,
                            'tempo_worklog_id' => $worklog['tempoWorklogId'] ?? $worklog['id'],
                            'worklog_date' => $worklog['startDate'],
                            'seconds_spent' => $worklog['timeSpentSeconds'],
                            'description' => $worklog['description'] ?? null
                        ]);
                        
                        $storedCount++;
                        $this->info("Stored worklog for " . $employee->name);
                    } catch (\Exception $e) {
                        $this->error("Failed to store worklog: " . $e->getMessage());
                        Log::error("Failed to store worklog", [
                            'error' => $e->getMessage(),
                            'worklog' => $worklog,
                            'employee_id' => $employee->id
                        ]);
                        throw $e;
                    }
                }

                DB::commit();
                $this->info("\nSuccessfully stored {$storedCount} worklogs");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Transaction failed: " . $e->getMessage());
                Log::error('Transaction failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error('Worklog test failed', [
                'epic_key' => $epicKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function formatSeconds($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return "{$hours}h {$minutes}m";
    }
} 