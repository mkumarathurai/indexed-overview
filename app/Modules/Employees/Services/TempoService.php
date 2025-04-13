<?php

namespace App\Modules\Employees\Services;

use App\Modules\Employees\Models\Employee;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TempoService
{
    private $baseUrl;
    private $apiToken;
    protected $client;

    public function __construct()
    {
        $this->baseUrl = config('services.tempo.base_url', 'https://api.tempo.io/core/3');
        $this->apiToken = config('services.tempo.api_token');

        if (!$this->apiToken) {
            Log::error('Tempo configuration is incomplete', [
                'base_url' => $this->baseUrl,
                'api_token' => $this->apiToken ? 'set' : 'missing'
            ]);
        }

        $this->client = Http::withBasicAuth(
            config('services.jira.email'),
            config('services.jira.api_token')
        )->baseUrl(config('services.jira.base_url'));
    }

    /**
     * Get worklogs for an employee in a date range
     */
    public function getWorklogs(Employee $employee, Carbon $from, Carbon $to)
    {
        try {
            if (!$employee->external_id) {
                Log::warning('Employee has no external ID', ['employee_id' => $employee->id]);
                return [];
            }

            Log::info('DEBUG: Starting worklog fetch from Tempo', [
                'employee' => [
                    'id' => $employee->id,
                    'email' => $employee->email,
                    'external_id' => $employee->external_id
                ],
                'date_range' => [
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d')
                ]
            ]);

            // Get worklogs from Tempo API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken
            ])->get($this->baseUrl . '/worklogs', [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'worker' => $employee->external_id,
                'limit' => 100
            ]);

            if (!$response->successful()) {
                Log::error('DEBUG: Tempo worklog request failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'employee_id' => $employee->id
                ]);
                return [];
            }

            $data = $response->json();
            Log::info('DEBUG: Tempo API response', [
                'total_worklogs' => $data['metadata']['count'] ?? 0,
                'results_count' => count($data['results'] ?? [])
            ]);

            $worklogs = [];

            foreach ($data['results'] ?? [] as $worklog) {
                $worklogDate = Carbon::parse($worklog['startDate']);
                
                // Skip if not in our date range (shouldn't happen due to API params, but just in case)
                if (!$worklogDate->between($from, $to)) {
                    continue;
                }

                Log::info('DEBUG: Processing worklog', [
                    'author_id' => $worklog['author']['accountId'],
                    'employee_external_id' => $employee->external_id,
                    'started' => $worklog['startDate'],
                    'time_spent' => $worklog['timeSpentSeconds'],
                    'date' => $worklogDate->format('Y-m-d')
                ]);

                // Get issue details from Jira
                $issueResponse = Http::withBasicAuth(config('services.jira.email'), config('services.jira.api_token'))
                    ->get(config('services.jira.base_url') . "/rest/api/3/issue/{$worklog['issue']['id']}", [
                        'fields' => 'project,summary,status'
                    ]);

                if (!$issueResponse->successful()) {
                    Log::warning('DEBUG: Failed to get issue details', [
                        'issue_id' => $worklog['issue']['id'],
                        'status' => $issueResponse->status()
                    ]);
                    continue;
                }

                $issue = $issueResponse->json();

                $worklogs[] = [
                    'issue' => [
                        'key' => $issue['key'],
                        'project' => [
                            'key' => $issue['fields']['project']['key'],
                            'name' => $issue['fields']['project']['name']
                        ]
                    ],
                    'timeSpentSeconds' => $worklog['timeSpentSeconds'],
                    'startDate' => $worklog['startDate'],
                    'description' => $worklog['description']
                ];

                Log::info('DEBUG: Added worklog', [
                    'issue_key' => $issue['key'],
                    'project_key' => $issue['fields']['project']['key'],
                    'hours' => $worklog['timeSpentSeconds'] / 3600,
                    'date' => $worklogDate->format('Y-m-d')
                ]);
            }

            Log::info('DEBUG: Final worklog results', [
                'employee_id' => $employee->id,
                'total_worklogs' => count($worklogs),
                'worklogs' => array_map(function($w) {
                    return [
                        'issue_key' => $w['issue']['key'],
                        'project_key' => $w['issue']['project']['key'],
                        'hours' => $w['timeSpentSeconds'] / 3600,
                        'date' => Carbon::parse($w['startDate'])->format('Y-m-d')
                    ];
                }, $worklogs)
            ]);

            return $worklogs;
        } catch (\Exception $e) {
            Log::error('DEBUG: Error fetching worklogs', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Get worklogs grouped by project for an employee in a date range
     */
    public function getWorklogsByProject(Employee $employee, Carbon $from, Carbon $to)
    {
        $worklogs = $this->getWorklogs($employee, $from, $to);
        $projects = [];

        Log::info('DEBUG: Starting project grouping', [
            'employee_id' => $employee->id,
            'worklog_count' => count($worklogs)
        ]);

        foreach ($worklogs as $worklog) {
            $projectKey = $worklog['issue']['project']['key'];
            $hours = $worklog['timeSpentSeconds'] / 3600;

            Log::info('DEBUG: Processing worklog for project', [
                'project_key' => $projectKey,
                'hours' => $hours
            ]);

            if (!isset($projects[$projectKey])) {
                $projects[$projectKey] = [
                    'key' => $projectKey,
                    'name' => $worklog['issue']['project']['name'],
                    'hours' => 0,
                    'worklogs' => []
                ];
            }

            $projects[$projectKey]['hours'] += $hours;
            $projects[$projectKey]['worklogs'][] = [
                'date' => Carbon::parse($worklog['startDate'])->format('Y-m-d'),
                'hours' => $hours,
                'description' => $worklog['description']
            ];

            Log::info('DEBUG: Updated project hours', [
                'project_key' => $projectKey,
                'total_hours' => $projects[$projectKey]['hours']
            ]);
        }

        $finalProjects = array_values($projects);
        
        Log::info('DEBUG: Final project results', [
            'employee_id' => $employee->id,
            'project_count' => count($finalProjects),
            'total_hours' => array_sum(array_column($finalProjects, 'hours')),
            'projects' => array_map(function($p) {
                return [
                    'key' => $p['key'],
                    'hours' => $p['hours']
                ];
            }, $finalProjects)
        ]);

        return $finalProjects;
    }

    /**
     * Test Jira API connection and worklog fetching
     */
    public function testJiraConnection(Employee $employee)
    {
        try {
            Log::info('TEST: Starting Jira API test', [
                'employee' => [
                    'id' => $employee->id,
                    'email' => $employee->email,
                    'external_id' => $employee->external_id
                ]
            ]);

            // Test 1: Basic API connection
            $response = Http::withBasicAuth(config('services.jira.email'), config('services.jira.api_token'))
                ->get(config('services.jira.base_url') . "/rest/api/3/myself");

            if (!$response->successful()) {
                Log::error('TEST: Basic API connection failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return false;
            }

            Log::info('TEST: Basic API connection successful');

            // Test 2: Simple issue search
            $jql = "project IS NOT EMPTY";
            $response = Http::withBasicAuth(config('services.jira.email'), config('services.jira.api_token'))
                ->get(config('services.jira.base_url') . "/rest/api/3/search", [
                    'jql' => $jql,
                    'maxResults' => 1
                ]);

            if (!$response->successful()) {
                Log::error('TEST: Issue search failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return false;
            }

            Log::info('TEST: Issue search successful');

            // Test 3: Worklog search with different JQL variations
            $jqlVariations = [
                "worklogAuthor = '{$employee->external_id}'",
                "worklogAuthor = '{$employee->email}'",
                "assignee = '{$employee->external_id}'",
                "assignee = '{$employee->email}'"
            ];

            foreach ($jqlVariations as $jql) {
                Log::info('TEST: Trying JQL variation', ['jql' => $jql]);
                
                $response = Http::withBasicAuth(config('services.jira.email'), config('services.jira.api_token'))
                    ->get(config('services.jira.base_url') . "/rest/api/3/search", [
                        'jql' => $jql,
                        'fields' => 'project,summary,status,worklog',
                        'maxResults' => 1,
                        'expand' => 'worklog'
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    Log::info('TEST: Worklog search successful', [
                        'jql' => $jql,
                        'total_issues' => $data['total'] ?? 0,
                        'has_worklogs' => isset($data['issues'][0]['fields']['worklog']['worklogs'])
                    ]);

                    if (isset($data['issues'][0]['fields']['worklog']['worklogs'])) {
                        $worklog = $data['issues'][0]['fields']['worklog']['worklogs'][0];
                        Log::info('TEST: Sample worklog data', [
                            'issue_key' => $data['issues'][0]['key'],
                            'worklog_count' => count($data['issues'][0]['fields']['worklog']['worklogs']),
                            'sample_worklog' => $worklog
                        ]);
                    }
                } else {
                    Log::error('TEST: Worklog search failed', [
                        'jql' => $jql,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('TEST: Error during Jira API test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function getWorklogsForIssue(string $issueKey)
    {
        try {
            $response = Http::withBasicAuth(
                config('services.jira.email'),
                config('services.jira.api_token')
            )->get(config('services.jira.base_url') . "/rest/api/3/issue/{$issueKey}/worklog");

            if (!$response->successful()) {
                Log::error('Failed to fetch worklogs for issue', [
                    'issue_key' => $issueKey,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return [];
            }

            $data = $response->json();
            return $data['worklogs'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch worklogs for issue', [
                'issue_key' => $issueKey,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
} 