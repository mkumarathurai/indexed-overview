<?php

namespace App\Modules\Employees\Http\Livewire;

use Livewire\Component;
use App\Modules\Employees\Models\Employee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Modules\Employees\Services\JiraProjectService;
use App\Modules\Employees\Services\TempoService;

class EmployeeDetails extends Component
{
    public $employee;
    public $editing = false;
    public $formData = [];
    public $projects = [];
    public $otherEmployeesProjects = [];

    protected $jiraProjectService;

    public function boot(JiraProjectService $jiraProjectService)
    {
        $this->jiraProjectService = $jiraProjectService;
    }

    protected function rules()
    {
        return [
            'formData.title' => 'nullable|string|max:255',
            'formData.work_phone' => 'nullable|string|max:50',
            'formData.start_date' => 'nullable|date',
        ];
    }

    protected $listeners = ['refreshEmployee' => '$refresh'];

    public function mount(Employee $employee)
    {
        $this->employee = $employee;
        $this->initializeFormData();
        
        // Load projects
        $this->loadProjects();
        
        Log::info('EmployeeDetails mounted', [
            'employee_id' => $this->employee->id,
            'editing' => $this->editing,
            'form_data' => $this->formData,
            'projects_count' => count($this->projects)
        ]);
    }

    public function hydrate()
    {
        Log::info('Component hydrating', [
            'employee_id' => $this->employee->id,
            'editing' => $this->editing
        ]);
    }

    public function updating($name, $value)
    {
        Log::info('EmployeeDetails property updating', [
            'property' => $name,
            'value' => $value,
            'employee_id' => $this->employee->id
        ]);
    }

    public function updated($name)
    {
        Log::info('EmployeeDetails property updated', [
            'property' => $name,
            'employee_id' => $this->employee->id,
            'editing' => $this->editing
        ]);
    }

    public function enableEditing()
    {
        $this->editing = true;
        $this->initializeFormData();
        Log::info('Editing enabled', ['employee_id' => $this->employee->id]);
    }

    public function disableEditing()
    {
        $this->editing = false;
        Log::info('Editing disabled', ['employee_id' => $this->employee->id]);
    }

    public function toggleEditing()
    {
        $this->editing = !$this->editing;
        if ($this->editing) {
            $this->initializeFormData();
        }
    }

    private function initializeFormData()
    {
        try {
            $this->formData = [
                'title' => $this->employee->title,
                'work_phone' => $this->employee->work_phone,
                'start_date' => optional($this->employee->start_date)->format('Y-m-d')
            ];
            
            Log::info('Form data initialized', [
                'formData' => $this->formData
            ]);
        } catch (\Exception $e) {
            Log::error('Error initializing form data', [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function loadProjects()
    {
        try {
            $jiraProjectService = app(JiraProjectService::class);
            $this->projects = $jiraProjectService->getEmployeeProjects($this->employee);
            
            Log::info('Projects loaded', [
                'employee_id' => $this->employee->id,
                'project_count' => count($this->projects)
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading projects', [
                'employee_id' => $this->employee->id,
                'error' => $e->getMessage()
            ]);
            $this->projects = [];
        }
    }

    private function loadOtherEmployeesProjects()
    {
        // Implementation of loadOtherEmployeesProjects method
    }

    public function save()
    {
        try {
            $updates = array_filter($this->formData, function($value) {
                return $value !== '' && $value !== null;
            });
            
            Log::info('Attempting to save employee updates', [
                'employee_id' => $this->employee->id,
                'updates' => $updates
            ]);
            
            $this->employee->update($updates);
            $this->editing = false;
            $this->emit('employeeUpdated');
            
            session()->flash('message', 'Employee details updated successfully.');
            Log::info('Employee details saved successfully');
        } catch (\Exception $e) {
            Log::error('Error saving employee details', [
                'employee_id' => $this->employee->id,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to update employee details.');
        }
    }

    public function cancel()
    {
        $this->editing = false;
        $this->initializeFormData();
        Log::info('Edit cancelled, form data reset', [
            'employee_id' => $this->employee->id
        ]);
    }

    public function dehydrate()
    {
        Log::info('Component dehydrating', [
            'employee_id' => $this->employee->id,
            'editing' => $this->editing
        ]);
    }

    public function render()
    {
        Log::info('Rendering component', [
            'employee_id' => $this->employee->id,
            'editing' => $this->editing
        ]);
        
        return view('employees::livewire.employee-details');
    }
}