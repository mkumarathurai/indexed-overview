<?php

namespace App\Modules\TempoHours\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Modules\TempoHours\Models\TempoWorklog;

class TempoPdfExport
{
    public function generatePdf($projectKey, $period)
    {
        $worklogs = TempoWorklog::where('project_key', $projectKey)
            ->whereBetween('started_at', [
                Carbon::createFromFormat('Y-m', $period)->startOfMonth(),
                Carbon::createFromFormat('Y-m', $period)->endOfMonth()
            ])
            ->orderBy('started_at')
            ->get();

        $pdf = Pdf::loadView('tempo-hours::pdf.timesheet', [
            'worklogs' => $worklogs,
            'projectKey' => $projectKey,
            'period' => $period
        ]);

        return $pdf;
    }
}
