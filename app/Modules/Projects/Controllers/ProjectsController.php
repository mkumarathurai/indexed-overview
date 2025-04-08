<?php

namespace App\Modules\Projects\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Services\ProjectsApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    protected $projectsApiService;

    public function __construct(ProjectsApiService $projectsApiService)
    {
        $this->projectsApiService = $projectsApiService;
    }

    public function index(Request $request)
    {
        // Get selected period or default to current month
        $selectedPeriod = $request->get('period', Carbon::now()->format('Y-m'));
        list($year, $month) = explode('-', $selectedPeriod);
        
        // Get available periods (Jul 2024 - Jun 2025)
        $periods = $this->getAvailablePeriods();
        
        // Get projects with hours
        $projects = $this->projectsApiService->getProjects(true);
        $projectHours = $this->projectsApiService->getProjectHoursForPeriod($selectedPeriod);
        
        // Merge hours data with projects
        foreach ($projects['values'] as &$project) {
            $hours = $projectHours[$project['key']] ?? null;
            $project['monthly_hours'] = $hours?->monthly_hours ?? 0;
            $project['invoice_ready_hours'] = $hours?->invoice_ready_hours ?? 0;
        }

        return view('projects::projects-index', [
            'projects' => array_values($projects['values']), // Ensure sequential array
            'selectedPeriod' => $selectedPeriod,
            'periods' => $periods,
            'totalMonthlyHours' => collect($projectHours)->sum('monthly_hours'),
            'totalInvoiceReadyHours' => collect($projectHours)->sum('invoice_ready_hours')
        ]);
    }

    public function show($projectKey)
    {
        $projects = $this->projectsApiService->getProjects(true);
        $project = collect($projects['values'])->firstWhere('key', $projectKey);
        
        if (!$project) {
            abort(404);
        }

        // Hent projekt timer og issues
        $projectData = $this->projectsApiService->getProjectDetails($projectKey);
        
        return view('projects::project-details', [
            'project' => $project,
            'totalHours' => $projectData['totalHours'] ?? 0,
            'invoiceReadyHours' => $projectData['invoiceReadyHours'] ?? 0,
            'issues' => $projectData['issues'] ?? []
        ]);
    }

    public function refresh(Request $request)
    {
        $period = $request->get('period', Carbon::now()->format('Y-m'));
        $this->projectsApiService->refreshProjectHours($period);
        
        return redirect()->route('projects.index', ['period' => $period]);
    }

    protected function getAvailablePeriods()
    {
        $periods = [];
        $start = Carbon::create(2024, 7, 1);
        $end = Carbon::create(2025, 6, 1);

        while ($start <= $end) {
            $periods[] = [
                'value' => $start->format('Y-m'),
                'label' => $start->format('F Y')
            ];
            $start->addMonth();
        }

        return $periods;
    }
}