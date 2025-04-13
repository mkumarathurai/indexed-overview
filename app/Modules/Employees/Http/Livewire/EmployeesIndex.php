<?php

namespace App\Modules\Employees\Http\Livewire;

use Livewire\Component;
use App\Modules\Employees\Models\Employee;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Services\JiraClient;

class EmployeesIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $isLoading = false;
    
    protected $queryString = ['search', 'sortField', 'sortDirection'];
    
    protected $listeners = [
        'refreshComponent' => '$refresh'
    ];
    
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
    
    public function refreshEmployees()
    {
        $jiraClient = new JiraClient(
            config('services.jira.api_token'),
            config('services.jira.base_url')
        );

        try {
            $response = $jiraClient->get('/rest/api/3/users');
            
            foreach ($response->json() as $user) {
                if (isset($user['emailAddress']) && str_ends_with($user['emailAddress'], '@indexed.dk')) {
                    Employee::updateOrCreate(
                        ['email' => $user['emailAddress']],
                        [
                            'name' => $user['displayName'],
                            'avatar' => $user['avatarUrls']['48x48'] ?? null,
                            'external_url' => $user['self'] ?? null,
                            'status' => $user['active'] ? 'active' : 'inactive',
                            'type' => 'employee',
                            'external_source' => 'jira'
                        ]
                    );
                }
            }

            session()->flash('success', 'Employees synced successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to sync employees.');
        }
    }
    
    public function render()
    {
        $this->isLoading = true;
        
        try {
            $query = Employee::query()
                ->where('status', 'active')
                ->where('type', 'employee')
                ->when($this->search, function ($query) {
                    return $query->where(function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%')
                            ->orWhere('title', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy($this->sortField, $this->sortDirection);
            
            $employees = $query->paginate(10);
            
            $this->isLoading = false;
            
            return view('employees::livewire.employees-index', [
                'employees' => $employees
            ]);
        } catch (\Exception $e) {
            Log::error('Error in EmployeesIndex: ' . $e->getMessage());
            $this->isLoading = false;
            return view('employees::livewire.employees-index', [
                'employees' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10)
            ]);
        }
    }
}