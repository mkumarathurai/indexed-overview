<?php

namespace App\Modules\TempoHours\Http\Web\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\TempoHours\Models\TempoHours;
use App\Modules\TempoHours\Models\TempoWorklog;
use App\Modules\TempoHours\Services\TempoPdfExport;
use Carbon\Carbon;

class TempoHoursController extends Controller
{
    public function details($projectKey)
    {
        $project = TempoHours::where('project_key', $projectKey)->firstOrFail();
        
        // Load worklogs for the current period
        $period = request('period', now()->format('Y-m'));
        $project->setRelation('worklogs', TempoWorklog::where('project_key', $projectKey)
            ->whereBetween('started_at', [
                Carbon::createFromFormat('Y-m', $period)->startOfMonth(),
                Carbon::createFromFormat('Y-m', $period)->endOfMonth()
            ])
            ->orderBy('started_at', 'desc')
            ->get()
        );

        return view('tempo-hours::admin.tempo-hours.details', compact('project'));
    }

    public function downloadPdf($projectKey)
    {
        try {
            $period = request()->input('period', now()->format('Y-m'));
            $project = TempoHours::where('project_key', $projectKey)
                ->where('period', $period)
                ->firstOrFail();

            $pdfExport = new TempoPdfExport();
            $pdf = $pdfExport->generatePdf($projectKey, $period);
            
            return $pdf->download("timesheet-{$projectKey}-{$period}.pdf");
        } catch (\Exception $e) {
            return back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }
}
