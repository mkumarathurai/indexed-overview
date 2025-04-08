<?php

namespace App\Modules\Projects\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ProjectsApiService
{
    protected $baseUrl;
    protected $apiToken;
    protected $email;

    public function __construct()
    {
        $this->baseUrl = config('projects.jira_base_url');
        $this->apiToken = config('projects.jira_api_token');
        $this->email = config('projects.jira_email');
    }

    public function getProjects($includeArchived = true)
    {
        $response = Http::withBasicAuth($this->email, $this->apiToken)
            ->get("{$this->baseUrl}/rest/api/3/project/search", [
                'includeArchived' => $includeArchived
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch projects from Jira: ' . $response->body());
        }

        return $response->json();
    }

    public function getIssues($projectKey, $includeArchived = true)
    {
        $response = Http::withBasicAuth($this->email, $this->apiToken)
            ->get("{$this->baseUrl}/rest/api/3/search", [
                'jql' => "project = $projectKey",
                'includeArchivedProjects' => $includeArchived,
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch issues from Jira: ' . $response->body());
        }

        return $response->json();
    }

    public function getProjectDetails($projectKey)
    {
        $jql = "project = '$projectKey' AND status = 'Ready for invoicing'";
        $response = Http::withBasicAuth($this->email, $this->apiToken)
            ->get("{$this->baseUrl}/rest/api/3/search", [
                'jql' => $jql,
                'fields' => 'summary,status,assignee,timetracking,worklog'
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch project details from Jira: ' . $response->body());
        }

        $issues = $response->json()['issues'];
        $totalHours = 0;
        $invoiceReadyHours = 0;

        foreach ($issues as &$issue) {
            $hours = ($issue['fields']['timetracking']['timeSpentSeconds'] ?? 0) / 3600;
            $issue['hours'] = $hours;
            $totalHours += $hours;
            if ($issue['fields']['status']['name'] === 'Ready for invoicing') {
                $invoiceReadyHours += $hours;
            }
        }

        return [
            'totalHours' => $totalHours,
            'invoiceReadyHours' => $invoiceReadyHours,
            'issues' => $issues
        ];
    }

    public function getProjectHours($projectKey, $startDate, $endDate)
    {
        // Implementation for fetching project hours
    }

    public function getBatchMonthlyHours($projectKeys, $startDate, $endDate)
    {
        // Implementation for fetching monthly hours for multiple projects
    }

    public function getBatchInvoiceReadyHours($projectKeys)
    {
        // Implementation for fetching invoice-ready hours for multiple projects
    }

    public function getProjectHoursForPeriod($period)
    {
        return DB::table('project_hours')
            ->where('period', $period)
            ->get()
            ->keyBy('project_key');
    }

    public function refreshProjectHours($period)
    {
        $startDate = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get all projects
        $projects = $this->getProjects(true)['values'];

        foreach ($projects as $project) {
            // Get monthly hours
            $monthlyHours = $this->getProjectMonthlyHours($project['key'], $startDate, $endDate);
            
            // Get invoice ready hours (regardless of period)
            $invoiceReadyHours = $this->getInvoiceReadyHours($project['key']);

            // Update database
            DB::table('project_hours')->updateOrInsert(
                [
                    'project_key' => $project['key'],
                    'period' => $period
                ],
                [
                    'monthly_hours' => $monthlyHours,
                    'invoice_ready_hours' => $invoiceReadyHours,
                    'last_synced_at' => now()
                ]
            );
        }
    }

    public function getProject($projectKey)
    {
        $projects = $this->getProjects(true);
        return collect($projects['values'])->firstWhere('key', $projectKey);
    }

    public function getInvoiceReadyIssues($projectKey)
    {
        $response = Http::withBasicAuth($this->email, $this->apiToken)
            ->get("{$this->baseUrl}/rest/api/3/search", [
                'jql' => "project = {$projectKey} AND status = 'Ready for invoicing'",
                'fields' => 'summary,status,worklog'
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch invoice ready issues: ' . $response->body());
        }

        $issues = $response->json()['issues'];

        // Calculate hours for each issue
        foreach ($issues as &$issue) {
            $worklogSeconds = collect($issue['fields']['worklog']['worklogs'] ?? [])
                ->sum('timeSpentSeconds');
            $issue['hours'] = round($worklogSeconds / 3600, 1);
        }

        return $issues;
    }

    protected function getProjectMonthlyHours($projectKey, $startDate, $endDate)
    {
        $cacheKey = "project_hours:{$projectKey}:{$startDate->format('Y-m')}";
        
        return Cache::remember($cacheKey, 60, function () use ($projectKey, $startDate, $endDate) {
            $response = Http::withBasicAuth($this->email, $this->apiToken)
                ->get("{$this->baseUrl}/rest/api/3/search", [
                    'jql' => "project = {$projectKey} AND worklogDate >= {$startDate->format('Y-m-d')} AND worklogDate <= {$endDate->format('Y-m-d')}",
                    'fields' => 'worklog'
                ]);

            if ($response->failed()) {
                return 0;
            }

            return $this->calculateHoursFromWorklogs($response->json());
        });
    }

    protected function getInvoiceReadyHours($projectKey)
    {
        $cacheKey = "invoice_ready_hours:{$projectKey}";
        
        return Cache::remember($cacheKey, 60, function () use ($projectKey) {
            $response = Http::withBasicAuth($this->email, $this->apiToken)
                ->get("{$this->baseUrl}/rest/api/3/search", [
                    'jql' => "project = {$projectKey} AND status = 'Ready for invoicing'",
                    'fields' => 'worklog'
                ]);

            if ($response->failed()) {
                return 0;
            }

            return $this->calculateHoursFromWorklogs($response->json());
        });
    }

    protected function calculateHoursFromWorklogs($data)
    {
        $totalSeconds = 0;
        foreach ($data['issues'] ?? [] as $issue) {
            foreach ($issue['fields']['worklog']['worklogs'] ?? [] as $worklog) {
                $totalSeconds += $worklog['timeSpentSeconds'];
            }
        }
        return round($totalSeconds / 3600, 1);
    }
}