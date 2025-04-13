<?php

namespace App\Modules\Holidays\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Employees\Models\Employee;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateHolidayBalances extends Command
{
    protected $signature = 'holidays:update-balances';
    protected $description = 'Calculate total hours logged in INTERNAL-9 per user';

    public function handle()
    {
        $this->info('Starting hour calculation...');

        // Get the epic details from Jira
        $epicKey = 'INTERNAL-9';
        $this->info("Fetching epic details for {$epicKey}...");

        $epicResponse = Http::withBasicAuth(
            config('services.jira.email'),
            config('services.jira.api_token')
        )->get(config('services.jira.base_url') . "/rest/api/3/issue/{$epicKey}", [
            'fields' => 'summary'
        ]);

        if (!$epicResponse->successful()) {
            $this->error("Failed to fetch epic details: " . $epicResponse->body());
            return 1;
        }

        $epicData = $epicResponse->json();
        $epicId = $epicData['id'];
        $this->info("Found epic ID: {$epicId}");

        // Get all linked issues using JQL
        $this->info("Fetching linked issues...");
        $jqlResponse = Http::withBasicAuth(
            config('services.jira.email'),
            config('services.jira.api_token')
        )->get(config('services.jira.base_url') . "/rest/api/3/search", [
            'jql' => "parent = {$epicKey} OR 'Epic Link' = {$epicKey}",
            'fields' => 'summary,key'
        ]);

        if (!$jqlResponse->successful()) {
            $this->error("Failed to fetch linked issues: " . $jqlResponse->body());
            return 1;
        }

        $linkedIssues = $jqlResponse->json()['issues'];
        $this->info("Found " . count($linkedIssues) . " linked issues");

        // Get all worklogs for the epic and its linked issues
        $this->info("Fetching worklogs...");
        $worklogsResponse = Http::withBasicAuth(
            config('services.jira.email'),
            config('services.jira.api_token')
        )->get(config('services.tempo.base_url') . "/4/worklogs", [
            'issueId' => $epicId
        ]);

        if (!$worklogsResponse->successful()) {
            $this->error("Failed to fetch worklogs: " . $worklogsResponse->body());
            return 1;
        }

        $worklogs = $worklogsResponse->json();
        $this->info("Found " . count($worklogs) . " worklogs");

        // Group worklogs by employee email
        $employeeWorklogs = [];
        foreach ($worklogs as $worklog) {
            $email = $worklog['author']['emailAddress'];
            if (!isset($employeeWorklogs[$email])) {
                $employeeWorklogs[$email] = [
                    'totalSeconds' => 0,
                    'name' => $worklog['author']['displayName']
                ];
            }
            $employeeWorklogs[$email]['totalSeconds'] += $worklog['timeSpentSeconds'];
        }

        // Display results
        $this->info("\nTotal hours logged per user:");
        $this->info("===========================");

        foreach ($employeeWorklogs as $email => $data) {
            $totalHours = $data['totalSeconds'] / 3600;
            $this->info("{$data['name']} ({$email}): {$totalHours} hours");
        }

        return 0;
    }
} 