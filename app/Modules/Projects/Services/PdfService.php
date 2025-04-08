<?php

namespace App\Modules\Projects\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PdfService
{
    public function generateTimesheet($project, $issues, $options = [])
    {
        $totalHours = collect($issues)->sum('hours');
        
        $pdf = PDF::loadView('projects::pdf.timesheet', [
            'project' => $project,
            'issues' => $issues,
            'totalHours' => $totalHours,
            'generatedAt' => Carbon::now(),
            'billingPeriod' => $options['billingPeriod'] ?? null,
        ]);

        return $pdf->download("Timesheet-{$project['key']}-" . Carbon::now()->format('Y-m-d') . ".pdf");
    }
}
