<?php

namespace App\Modules\Employees\Http\Livewire;

use Livewire\Component;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Services\JiraApiService;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class JiraUsersIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $type = '';  // Filter by type: employee or external
    public $isLoading = false;
    public $isRefreshing = false;
    
    protected $queryString = ['search', 'sortField', 'sortDirection', 'type'];
    
    public function mount()
    {
        $this->isLoading = false;
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
    
    public function updatingType()
    {
        $this->resetPage();
    }
    
    public function refreshJiraUsers()
    {
        $this->isRefreshing = true;
        
        try {
            // Run the command to sync users from Jira
            Log::info('Starting Jira user sync from UI');
            $exitCode = Artisan::call('jira:sync-users');
            $output = Artisan::output();
            
            Log::info('Jira user sync completed', [
                'exitCode' => $exitCode,
                'output' => $output
            ]);
            
            if ($exitCode === 0) {
                session()->flash('success', 'Employees synced successfully from Jira.');
            } else {
                session()->flash('error', 'Error syncing employees from Jira: ' . $output);
                Log::error('Error syncing Jira users via UI', ['output' => $output]);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error syncing employees from Jira: ' . $e->getMessage());
            Log::error('Exception when syncing Jira users via UI', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        $this->isRefreshing = false;
        
        // Refresh the component to show new data
        $this->resetPage();
        $this->emit('refreshComponent');
    }
    
    public function render(JiraApiService $jiraService)
    {
        $this->isLoading = true;
        
        try {
            $query = Employee::fromJira()
                ->where('status', 'active') // Only show active users
                ->when($this->search, function ($query) {
                    return $query->where(function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->type, function ($query) {
                    return $query->where('type', $this->type);
                })
                ->orderBy($this->sortField, $this->sortDirection);
            
            $employees = $query->paginate(10);
            
            // Get type counts for display
            $typeCounts = [
                'total' => Employee::fromJira()->where('status', 'active')->count(),
                'employee' => Employee::fromJira()->where('status', 'active')->where('type', 'employee')->count(),
                'external' => Employee::fromJira()->where('status', 'active')->where('type', 'external')->count(),
            ];
            
            $this->isLoading = false;
            
            return view('employees::livewire.jira-users-index', [
                'jiraUsers' => $employees,
                'baseUrl' => $jiraService->getUserProfileUrl(''),
                'typeCounts' => $typeCounts
            ]);
        } catch (\Exception $e) {
            Log::error('Error in JiraUsersIndex: ' . $e->getMessage());
            $this->isLoading = false;
            return view('employees::livewire.jira-users-index', [
                'jiraUsers' => collect(),
                'baseUrl' => $jiraService->getUserProfileUrl(''),
                'typeCounts' => ['total' => 0, 'employee' => 0, 'external' => 0]
            ]);
        }
    }
} 