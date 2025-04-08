<?php

namespace App\Modules\Projects\Http\Livewire;

use Livewire\Component;
use App\Modules\Projects\Services\ProjectsApiService;

class ProjectsIndex extends Component
{
    public $projects = [];
    public $monthlyHours = [];
    public $invoiceReadyHours = [];

    protected $projectsApiService;

    public function __construct()
    {
        $this->projectsApiService = new ProjectsApiService();
    }

    public function mount()
    {
        $this->loadProjects();
    }

    public function loadProjects()
    {
        $this->projects = $this->projectsApiService->getProjects();
        $this->monthlyHours = $this->projectsApiService->getBatchMonthlyHours($this->projects);
        $this->invoiceReadyHours = $this->projectsApiService->getBatchInvoiceReadyHours($this->projects);
    }

    public function render()
    {
        return view('projects::components.projects-index', [
            'projects' => $this->projects,
            'monthlyHours' => $this->monthlyHours,
            'invoiceReadyHours' => $this->invoiceReadyHours,
        ]);
    }
}