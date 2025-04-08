<?php

namespace App\Modules\Projects\Http\Livewire;

use Livewire\Component;
use App\Modules\Projects\Services\ProjectsApiService;

class ProjectDetails extends Component
{
    public $projectKey;
    public $projectData;
    public $readyForInvoicingHours;

    protected $projectsApiService;

    public function mount($projectKey)
    {
        $this->projectKey = $projectKey;
        $this->projectsApiService = new ProjectsApiService();
        $this->loadProjectDetails();
    }

    public function loadProjectDetails()
    {
        $this->projectData = $this->projectsApiService->getProjectDetails($this->projectKey);
        $this->readyForInvoicingHours = $this->calculateReadyForInvoicingHours();
    }

    public function calculateReadyForInvoicingHours()
    {
        // Logic to calculate ready for invoicing hours based on project data
        return $this->projectData['invoice_ready_hours'] ?? 0;
    }

    public function render()
    {
        return view('projects::components.project-details', [
            'project' => $this->projectData,
            'readyForInvoicingHours' => $this->readyForInvoicingHours,
        ]);
    }
}