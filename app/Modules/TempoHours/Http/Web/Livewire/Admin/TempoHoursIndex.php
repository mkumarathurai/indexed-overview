<?php

namespace App\Modules\TempoHours\Http\Web\Livewire\Admin;

use Livewire\Component;
use App\Modules\TempoHours\Models\TempoHours;
use App\Modules\TempoHours\Services\TempoApiService;
use Carbon\Carbon;

class TempoHoursIndex extends Component
{
    public $selectedMonth;
    public $months = [];
    public $totalProjects = 0;
    public $totalHours = 0;
    public $invoiceReadyHours = 0;
    public $lastSyncedAt = null;
    public $notification = null;
    public $notificationType = null;

    public function mount()
    {
        // Generate months for the dropdown (last 12 months)
        $this->months = collect(range(0, 11))->map(function ($i) {
            $date = Carbon::now()->subMonths($i);
            return [
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y')
            ];
        })->toArray();

        // Set default selected month to the most recent period with data
        $this->selectedMonth = TempoHours::orderBy('period', 'desc')
            ->value('period') ?? Carbon::now()->format('Y-m');

        $this->loadData();
    }

    public function updatedSelectedMonth()
    {
        $this->loadData();
    }

    public function refresh()
    {
        try {
            // Run the sync command directly
            \Illuminate\Support\Facades\Artisan::call('tempo:sync', [
                '--period' => $this->selectedMonth
            ]);

            // Reload data
            $this->loadData();

            // Show success message
            $this->notification = 'Data refreshed successfully.';
            $this->notificationType = 'success';
        } catch (\Exception $e) {
            $this->notification = 'Failed to refresh data: ' . $e->getMessage();
            $this->notificationType = 'error';
        }
    }

    protected function loadData()
    {
        $period = $this->selectedMonth;
        \Log::debug('Loading data for period', ['period' => $period]);

        // Get projects for the selected period
        $projects = TempoHours::where('period', $period)->get();
        \Log::debug('Found projects', ['count' => $projects->count(), 'projects' => $projects->toArray()]);

        // Calculate totals
        $this->totalProjects = $projects->count();
        $this->totalHours = $projects->sum('period_hours');
        $this->invoiceReadyHours = $projects->sum('invoice_ready_hours');
        $this->lastSyncedAt = $projects->max('last_synced_at');

        \Log::debug('Calculated totals', [
            'totalProjects' => $this->totalProjects,
            'totalHours' => $this->totalHours,
            'invoiceReadyHours' => $this->invoiceReadyHours,
        ]);
    }

    public function render()
    {
        return view('tempo-hours::admin.tempo-hours.index', [
            'projects' => TempoHours::where('period', $this->selectedMonth)
                ->orderBy('project_key')
                ->get(),
            'totalProjects' => $this->totalProjects ?? 0,
            'totalHours' => $this->totalHours ?? 0,
            'invoiceReadyHours' => $this->invoiceReadyHours ?? 0,
            'lastSyncedAt' => $this->lastSyncedAt,
            'notification' => $this->notification,
            'notificationType' => $this->notificationType,
            'months' => $this->months
        ]);
    }
}