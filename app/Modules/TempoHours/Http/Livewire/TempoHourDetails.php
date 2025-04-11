<?php

namespace App\Modules\TempoHours\Http\Livewire;

use Livewire\Component;
use App\Modules\TempoHours\Models\TempoHours;
use App\Modules\TempoHours\Models\TempoWorklog;
use App\Modules\TempoHours\Services\TempoPdfExport;
use Carbon\Carbon;

class TempoHourDetails extends Component
{
    public $projectKey;
    public $period;
    public $worklogs;
    
    public function mount($projectKey)
    {
        $this->projectKey = $projectKey;
        $this->period = Carbon::now()->format('Y-m');
        $this->loadWorklogs();
    }

    public function loadWorklogs()
    {
        $this->worklogs = TempoWorklog::where('project_key', $this->projectKey)
            ->whereBetween('started_at', [
                Carbon::createFromFormat('Y-m', $this->period)->startOfMonth(),
                Carbon::createFromFormat('Y-m', $this->period)->endOfMonth()
            ])
            ->orderBy('started_at', 'desc')
            ->get();
    }

    public function downloadPdf()
    {
        $pdfExport = new TempoPdfExport();
        $pdf = $pdfExport->generatePdf($this->projectKey, $this->period);
        
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, "timesheet-{$this->projectKey}-{$this->period}.pdf");
    }

    public function changePeriod($period)
    {
        $this->period = $period;
        $this->loadWorklogs();
    }

    public function getTotals()
    {
        return [
            'total_hours' => $this->worklogs->sum('time_spent_seconds') / 3600,
            'billable_hours' => $this->worklogs->where('is_invoice_ready', true)->sum('time_spent_seconds') / 3600
        ];
    }

    public function render()
    {
        return view('tempo-hours::tempo-hour-details', [
            'totals' => $this->getTotals()
        ]);
    }
}
