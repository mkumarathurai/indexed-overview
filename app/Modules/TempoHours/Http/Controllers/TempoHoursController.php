<?php

namespace App\Modules\TempoHours\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\TempoHours\Services\TempoApiService;
use App\Modules\TempoHours\Models\TempoHours;
use App\Modules\TempoHours\Services\TempoPdfExport;
use Barryvdh\DomPDF\Facade\Pdf;

class TempoHoursController extends Controller
{
    protected $tempoApiService;

    public function __construct(TempoApiService $tempoApiService)
    {
        $this->tempoApiService = $tempoApiService;
    }

    public function refresh(Request $request)
    {
        try {
            $period = $request->input('period', now()->format('Y-m'));
            
            // Run the sync command
            $output = [];
            $exitCode = 0;
            exec("php artisan tempo:sync --period={$period} 2>&1", $output, $exitCode);

            if ($exitCode !== 0) {
                return back()->with('error', 'Sync failed: ' . implode("\n", $output));
            }

            return back()->with('success', 'Data refreshed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
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

    public function details($projectKey)
    {
        $project = TempoHours::where('project_key', $projectKey)->firstOrFail();
        return view('tempo-hours::tempo-hour-details', compact('project'));
    }
}