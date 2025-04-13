<?php

namespace App\Modules\Employees\Services;

use App\Modules\Employees\Models\Employee;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class JiraProjectService
{
    private $baseUrl;
    private $username;
    private $apiToken;

    public function __construct()
    {
        $this->baseUrl = config('services.jira.base_url');
        $this->username = config('services.jira.email');
        $this->apiToken = config('services.jira.api_token');

        if (!$this->baseUrl || !$this->username || !$this->apiToken) {
            Log::error('Jira configuration is incomplete', [
                'base_url' => $this->baseUrl ? 'set' : 'missing',
                'email' => $this->username ? 'set' : 'missing',
                'api_token' => $this->apiToken ? 'set' : 'missing'
            ]);
        }
    }

    /**
     * Test Jira API connection and worklog fetching
     */
    public function testJiraConnection(Employee $employee)
    {
        try {
            // Test 1: Basic API connection
            Log::info('TEST: Testing basic Jira API connection');
            $testResponse = Http::withBasicAuth($this->username, $this->apiToken)
                ->get("{$this->baseUrl}/rest/api/3/myself");

            if (!$testResponse->successful()) {
                Log::error('TEST: Basic Jira API connection failed', [
                    'status' => $testResponse->status(),
                    'response' => $testResponse->json()
                ]);
                return false;
            }

            Log::info('TEST: Basic Jira API connection successful', [
                'user' => $testResponse->json()['displayName'] ?? 'Unknown'
            ]);

            // Test 2: Simple issue search
            Log::info('TEST: Testing issue search');
            $searchResponse = Http::withBasicAuth($this->username, $this->apiToken)
                ->get("{$this->baseUrl}/rest/api/3/search", [
                    'jql' => 'project IS NOT EMPTY',
                    'maxResults' => 1
                ]);

            if (!$searchResponse->successful()) {
                Log::error('TEST: Issue search failed', [
                    'status' => $searchResponse->status(),
                    'response' => $searchResponse->json()
                ]);
                return false;
            }

            Log::info('TEST: Issue search successful', [
                'total_issues' => $searchResponse->json()['total'] ?? 0
            ]);

            // Test 3: Worklog search for the employee
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
            
            Log::info('TEST: Testing worklog search', [
                'employee' => [
                    'id' => $employee->id,
                    'external_id' => $employee->external_id,
                    'email' => $employee->email
                ],
                'date_range' => [
                    'start' => $startOfMonth,
                    'end' => $endOfMonth
                ]
            ]);

            // Test with different JQL variations
            $jqlVariations = [
                "worklogAuthor = '{$employee->external_id}'",
                "worklogAuthor = '{$employee->email}'",
                "assignee = '{$employee->external_id}'",
                "assignee = '{$employee->email}'"
            ];

            foreach ($jqlVariations as $jql) {
                Log::info('TEST: Trying JQL variation', ['jql' => $jql]);
                
                $worklogResponse = Http::withBasicAuth($this->username, $this->apiToken)
                    ->get("{$this->baseUrl}/rest/api/3/search", [
                        'jql' => $jql,
                        'fields' => 'worklog',
                        'maxResults' => 10
                    ]);

                if ($worklogResponse->successful()) {
                    $data = $worklogResponse->json();
                    Log::info('TEST: Worklog search response', [
                        'jql' => $jql,
                        'total_issues' => $data['total'] ?? 0,
                        'has_issues' => !empty($data['issues']),
                        'first_issue' => $data['issues'][0]['key'] ?? 'none',
                        'has_worklogs' => isset($data['issues'][0]['fields']['worklog']['worklogs'])
                    ]);

                    if (!empty($data['issues'])) {
                        $issue = $data['issues'][0];
                        if (isset($issue['fields']['worklog']['worklogs'])) {
                            Log::info('TEST: Sample worklog data', [
                                'issue_key' => $issue['key'],
                                'worklog_count' => count($issue['fields']['worklog']['worklogs']),
                                'sample_worklog' => $issue['fields']['worklog']['worklogs'][0] ?? 'none'
                            ]);
                        }
                    }
                } else {
                    Log::error('TEST: Worklog search failed', [
                        'jql' => $jql,
                        'status' => $worklogResponse->status(),
                        'response' => $worklogResponse->json()
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

    /**
     * Get projects for an employee
     */
    public function getEmployeeProjects(Employee $employee)
    {
        try {
            Log::info('Fetching projects for employee', [
                'employee_id' => $employee->id,
                'email' => $employee->email,
                'external_id' => $employee->external_id
            ]);

            // Get projects where employee is assignee
            $jql = "project IS NOT EMPTY AND assignee = '{$employee->external_id}'";
            
            $response = Http::withBasicAuth($this->username, $this->apiToken)
                ->get("{$this->baseUrl}/rest/api/3/search", [
                    'jql' => $jql,
                    'fields' => 'project,summary,status,updated',
                    'maxResults' => 100
                ]);

            if (!$response->successful()) {
                Log::error('Error fetching projects', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'employee_id' => $employee->id
                ]);
                return [];
            }

            $data = $response->json();
            $projects = [];
            $seenProjects = [];

            foreach ($data['issues'] ?? [] as $issue) {
                $projectKey = $issue['fields']['project']['key'];
                
                // Only process each project once
                if (isset($seenProjects[$projectKey])) {
                    continue;
                }

                $project = $issue['fields']['project'];
                $seenProjects[$projectKey] = true;

                $projects[] = [
                    'key' => $projectKey,
                    'name' => $project['name'],
                    'description' => $project['description'] ?? null,
                    'status' => $issue['fields']['status']['name'],
                    'role' => $this->getProjectRole($employee, $projectKey),
                    'last_activity' => Carbon::parse($issue['fields']['updated'])->format('Y-m-d')
                ];
            }

            Log::info('Projects fetched successfully', [
                'employee_id' => $employee->id,
                'project_count' => count($projects)
            ]);

            return $projects;
        } catch (\Exception $e) {
            Log::error('Error fetching projects', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    private function getProjectRole(Employee $employee, string $projectKey)
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->apiToken)
                ->get("{$this->baseUrl}/rest/api/3/project/{$projectKey}/role");

            if (!$response->successful()) {
                Log::error('Error fetching project role', [
                    'employee_id' => $employee->id,
                    'project_key' => $projectKey,
                    'status' => $response->status()
                ]);
                return 'Team Member';
            }

            $roles = $response->json();
            
            // Check if we have the Administrators role URL
            if (isset($roles['Administrators'])) {
                // Fetch the actual role members
                $roleResponse = Http::withBasicAuth($this->username, $this->apiToken)
                    ->get($roles['Administrators']);

                if ($roleResponse->successful()) {
                    $roleData = $roleResponse->json();
                    
                    // Check if the employee is in the role members
                    if (isset($roleData['actors']) && is_array($roleData['actors'])) {
                        foreach ($roleData['actors'] as $actor) {
                            if (isset($actor['actorUser']['accountId']) && 
                                $actor['actorUser']['accountId'] === $employee->external_id) {
                                return 'Project Lead';
                            }
                        }
                    }
                }
            }

            return 'Team Member';
        } catch (\Exception $e) {
            Log::error('Error fetching project role', [
                'employee_id' => $employee->id,
                'project_key' => $projectKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 'Team Member';
        }
    }
}