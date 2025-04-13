<?php

namespace App\Modules\Holidays\Livewire;

use Livewire\Component;
use App\Modules\Employees\Models\Employee;
use App\Modules\Holidays\Models\HolidayWorklog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class HolidayList extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'worklog_date';
    public $sortDirection = 'desc';
    
    protected $queryString = ['search', 'sortField', 'sortDirection'];
    
    public function mount()
    {
        $this->loadWorklogs();
    }

    public function loadWorklogs()
    {
        try {
            Log::info("Fetching worklogs from INTERNAL-9");
            
            // Get all employees with @indexed.dk emails
            $employees = Employee::where('email', 'like', '%@indexed.dk')
                ->get()
                ->keyBy('email');

            // Get all worklogs for INTERNAL-9 and its linked issues
            $jqlResponse = Http::withBasicAuth(
                config('services.jira.email'),
                config('services.jira.api_token')
            )->get(config('services.jira.base_url') . "/rest/api/3/search", [
                'jql' => "parent = INTERNAL-9 OR 'Epic Link' = INTERNAL-9",
                'fields' => 'summary,key'
            ]);

            if (!$jqlResponse->successful()) {
                Log::error("Failed to fetch linked issues", [
                    'status' => $jqlResponse->status(),
                    'response' => $jqlResponse->body()
                ]);
                return;
            }

            $linkedIssues = $jqlResponse->json()['issues'];
            Log::info("Found " . count($linkedIssues) . " linked issues");

            DB::beginTransaction();
            try {
                // First, delete all existing worklogs for these employees
                $employeeIds = $employees->pluck('id')->toArray();
                HolidayWorklog::whereIn('employee_id', $employeeIds)->delete();

                // Get worklogs for each issue
                foreach ($linkedIssues as $issue) {
                    $issueKey = $issue['key'];
                    Log::info("Fetching worklogs for issue: " . $issueKey);

                    $worklogResponse = Http::withBasicAuth(
                        config('services.jira.email'),
                        config('services.jira.api_token')
                    )->get(config('services.jira.base_url') . "/rest/api/3/issue/{$issueKey}/worklog");

                    if (!$worklogResponse->successful()) {
                        Log::error("Failed to fetch worklogs for issue {$issueKey}", [
                            'status' => $worklogResponse->status(),
                            'response' => $worklogResponse->body()
                        ]);
                        continue;
                    }

                    $worklogs = $worklogResponse->json()['worklogs'];
                    Log::info("Found " . count($worklogs) . " worklogs for issue {$issueKey}");

                    foreach ($worklogs as $worklog) {
                        $email = $worklog['author']['emailAddress'] ?? null;
                        if (!$email || !isset($employees[$email])) {
                            Log::info("Skipping worklog for unknown employee: " . $email);
                            continue;
                        }

                        $employee = $employees[$email];
                        Log::info("Storing worklog for employee: " . $employee->name);

                        // Convert time spent to seconds if it's in a different format
                        $timeSpentSeconds = $worklog['timeSpentSeconds'];
                        if (!is_numeric($timeSpentSeconds)) {
                            // If timeSpentSeconds is not numeric, try to parse it from timeSpent
                            $timeSpent = $worklog['timeSpent'] ?? '';
                            if (preg_match('/(\d+)h/', $timeSpent, $hours)) {
                                $timeSpentSeconds = $hours[1] * 3600;
                            }
                            if (preg_match('/(\d+)m/', $timeSpent, $minutes)) {
                                $timeSpentSeconds += $minutes[1] * 60;
                            }
                        }

                        HolidayWorklog::create([
                            'employee_id' => $employee->id,
                            'tempo_worklog_id' => $worklog['id'],
                            'worklog_date' => $worklog['started'],
                            'seconds_spent' => $timeSpentSeconds,
                            'description' => $worklog['comment'] ?? 'No description'
                        ]);
                    }
                }

                DB::commit();
                session()->flash('success', 'Worklogs updated successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed to store worklogs", [
                    'error' => $e->getMessage()
                ]);
                session()->flash('error', 'Failed to update worklogs: ' . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            Log::error("Error in loadWorklogs", [
                'message' => $e->getMessage()
            ]);
            session()->flash('error', 'Error loading worklogs: ' . $e->getMessage());
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = HolidayWorklog::with('employee')
            ->when($this->search, function ($query) {
                return $query->whereHas('employee', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $worklogs = $query->paginate(10);
        
        return view('holidays::livewire.holiday-list', [
            'worklogs' => $worklogs
        ]);
    }
} 
} 




