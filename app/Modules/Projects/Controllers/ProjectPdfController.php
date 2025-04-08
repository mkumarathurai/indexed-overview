<?php

namespace App\Modules\Projects\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Services\ProjectsApiService;
use App\Modules\Projects\Services\PdfService;
use Carbon\Carbon;

class ProjectPdfController extends Controller
{
    protected $projectsApiService;
    protected $pdfService;

    public function __construct(ProjectsApiService $projectsApiService, PdfService $pdfService)
    {
        $this->projectsApiService = $projectsApiService;
        $this->pdfService = $pdfService;
    }

    public function download($projectKey)
    {
        $project = $this->projectsApiService->getProject($projectKey);
        
        if (!$project) {
            abort(404, 'Project not found');
        }

        $invoiceReadyIssues = $this->projectsApiService->getInvoiceReadyIssues($projectKey);
        
        // Create billing period
        $currentDate = Carbon::now();
        $startDate = $currentDate->copy()->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();
        
        return $this->pdfService->generateTimesheet($project, $invoiceReadyIssues, [
            'billingPeriod' => [
                'start' => $startDate->format('d.m.Y'),
                'end' => $endDate->format('d.m.Y')
            ]
        ]);
    }
}
