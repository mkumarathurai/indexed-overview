<?php

namespace App\Modules\TempoHours\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Modules\TempoHours\Models\TempoWorklog;
use App\Modules\TempoHours\Models\TempoHours;
use App\Services\JiraClient;
use Exception;

class TempoApiService
{
    protected $baseUrl;
    protected $apiToken;
    private $jiraClient;

    public function __construct()
    {
        $this->baseUrl = config('services.tempo.base_url');
        $this->apiToken = config('services.tempo.api_token');
        $this->jiraClient = new JiraClient(
            config('services.jira.api_token'),
            config('services.jira.base_url')
        );
    }

    public function getWorklogs($startDate, $endDate)
    {
        $cacheKey = "tempo_worklogs_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, 60, function () use ($startDate, $endDate) {
            try {
                Log::info('Sending request to Tempo API', [
                    'url' => "{$this->baseUrl}/worklogs",
                    'parameters' => ['dateFrom' => $startDate, 'dateTo' => $endDate],
                ]);

                $response = Http::withToken($this->apiToken)
                    ->get("{$this->baseUrl}/worklogs", [
                        'dateFrom' => $startDate,
                        'dateTo' => $endDate,
                        'limit' => 1000,
                    ]);

                $response->throw();
                $data = $response->json();

                Log::debug('Tempo API response', ['data' => $data]);

                return $data['results'] ?? [];
            } catch (\Illuminate\Http\Client\RequestException $e) {
                Log::error('Tempo API Request failed', [
                    'url' => "{$this->baseUrl}/worklogs",
                    'parameters' => ['dateFrom' => $startDate, 'dateTo' => $endDate],
                    'status' => $e->response->status(),
                    'body' => $e->response->body(),
                ]);
                throw $e;
            }
        });
    }

    public function getProjectHours($projectKey, $period)
    {
        $cacheKey = "tempo_project_hours_{$projectKey}_{$period}";
        
        return Cache::remember($cacheKey, 60, function () use ($projectKey, $period) {
            try {
                $response = Http::withToken($this->apiToken)
                    ->get("{$this->baseUrl}/worklogs/project/{$projectKey}", [
                        'period' => $period
                    ]);

                $response->throw();

                return $response->json();
            } catch (\Illuminate\Http\Client\RequestException $e) {
                Log::error('Tempo API Request failed', [
                    'url' => "{$this->baseUrl}/worklogs/project/{$projectKey}",
                    'parameters' => ['period' => $period],
                    'status' => $e->response->status(),
                    'body' => $e->response->body(),
                ]);
                throw $e;
            }
        });
    }

    public function getAccounts()
    {
        return Cache::remember('tempo_accounts', 360, function () {
            try {
                $response = Http::withToken($this->apiToken)
                    ->get("{$this->baseUrl}/accounts");

                $response->throw();

                return $response->json();
            } catch (\Illuminate\Http\Client\RequestException $e) {
                Log::error('Tempo API Request failed', [
                    'url' => "{$this->baseUrl}/accounts",
                    'status' => $e->response->status(),
                    'body' => $e->response->body(),
                ]);
                throw $e;
            }
        });
    }

    private function updateProjectTotals($period)
    {
        // Parse period into start and end dates
        $startDate = Carbon::parse($period . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get all unique project keys from worklogs
        $projectKeys = TempoWorklog::whereBetween('started_at', [$startDate, $endDate])
            ->distinct()
            ->pluck('project_key');

        foreach ($projectKeys as $projectKey) {
            // Get project name from Jira
            try {
                $response = $this->jiraClient->get("/rest/api/2/project/{$projectKey}");
                $projectName = $response->successful() ? $response->json()['name'] : null;
            } catch (\Exception $e) {
                Log::error('Error fetching project name from Jira', [
                    'project_key' => $projectKey,
                    'error' => $e->getMessage()
                ]);
                $projectName = null;
            }

            // Calculate total hours and invoice-ready hours
            $worklogs = TempoWorklog::where('project_key', $projectKey)
                ->whereBetween('started_at', [$startDate, $endDate])
                ->get();

            $periodHours = $worklogs->sum('time_spent_seconds') / 3600; // Convert seconds to hours
            $invoiceReadyHours = $worklogs->where('is_invoice_ready', true)->sum('time_spent_seconds') / 3600; // Convert seconds to hours

            // Update or create the record
            TempoHours::updateOrCreate(
                [
                    'project_key' => $projectKey,
                    'period' => $period
                ],
                [
                    'name' => $projectName,
                    'period_hours' => $periodHours,
                    'invoice_ready_hours' => $invoiceReadyHours,
                    'last_synced_at' => now()
                ]
            );
        }
    }

    public function syncWorklogs(string $period): void
    {
        // Parse period into start and end dates
        $startDate = Carbon::parse($period . '-01')->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($period . '-01')->endOfMonth()->format('Y-m-d');

        try {
            Log::info('Fetching worklogs from Tempo API', [
                'period' => $period,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            $worklogs = $this->getWorklogs($startDate, $endDate);
            $count = count($worklogs);

            Log::info('Processing worklogs', ['count' => $count]);

            $processed = 0;
            $skipped = 0;

            foreach ($worklogs as $worklog) {
                try {
                    $data = $this->processWorklog($worklog);
                    if (!empty($data)) {
                        TempoWorklog::updateOrCreate(
                            ['tempo_worklog_id' => $data['tempo_worklog_id']],
                            $data
                        );
                        $processed++;
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing worklog', [
                        'worklog' => $worklog,
                        'error' => $e->getMessage()
                    ]);
                    $skipped++;
                }
            }

            Log::info('Sync completed', [
                'period' => $period,
                'total' => $count,
                'processed' => $processed,
                'skipped' => $skipped
            ]);

            // Update project totals after processing worklogs
            $this->updateProjectTotals($period);
        } catch (\Exception $e) {
            Log::error('Error fetching worklogs from Tempo API', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function processWorklog(array $worklog): array
    {
        Log::debug('Processing worklog', ['worklog' => $worklog]);

        // Extract the issue key from the description if not available in the issue object
        $issueKey = null;
        
        // Try to get issue key from the description
        if (preg_match('/(?:Working on issue |issue )([A-Z]+-\d+)/', $worklog['description'], $matches)) {
            $issueKey = $matches[1];
            Log::debug('Found issue key in description', ['issue_key' => $issueKey]);
        }
        
        // If not found in description, try to get it from the issue URL
        if (!$issueKey && isset($worklog['issue']['self'])) {
            if (preg_match('/\/([A-Z]+-\d+)$/', $worklog['issue']['self'], $matches)) {
                $issueKey = $matches[1];
                Log::debug('Found issue key in issue URL', ['issue_key' => $issueKey]);
            }
        }

        if (!$issueKey) {
            throw new \Exception('Could not find issue key in worklog');
        }

        // Extract project key from issue key
        $projectKey = explode('-', $issueKey)[0];

        // Combine date and time
        $startedAt = Carbon::parse($worklog['startDate'] . ' ' . $worklog['startTime']);

        return [
            'tempo_worklog_id' => (string)$worklog['tempoWorklogId'],
            'issue_key' => $issueKey,
            'project_key' => $projectKey,
            'started_at' => $startedAt,
            'time_spent_seconds' => $worklog['timeSpentSeconds'],
            'billable_seconds' => $worklog['billableSeconds'],
            'author_account_id' => $worklog['author']['accountId'],
            'description' => $worklog['description'],
            'is_invoice_ready' => $worklog['billableSeconds'] > 0
        ];
    }
}
