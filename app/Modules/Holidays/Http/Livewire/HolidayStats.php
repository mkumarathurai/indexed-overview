<?php

namespace App\Modules\Holidays\Http\Livewire;

use App\Modules\Employees\Models\Employee;
use App\Modules\Holidays\Models\Holiday;
use Carbon\Carbon;
use Livewire\Component;

class HolidayStats extends Component
{
    public $totalEmployees = 0;
    public $totalDaysTaken = 0;
    public $averageDaysPerEmployee = 0;
    public $lastSynced = null;

    protected $listeners = ['holidaysUpdated' => 'loadStats'];

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        // Count active employees
        $this->totalEmployees = Employee::where('status', 'active')
            ->where('type', 'employee')
            ->count();

        // Calculate total days taken this year
        $currentYear = Carbon::now()->year;
        
        $this->totalDaysTaken = Holiday::where('year', $currentYear)
            ->sum('used_days');

        // Calculate average days per employee
        $this->averageDaysPerEmployee = $this->totalEmployees > 0 
            ? round($this->totalDaysTaken / $this->totalEmployees, 1) 
            : 0;

        // Get last sync time
        $lastHoliday = Holiday::latest('updated_at')->first();
        $this->lastSynced = $lastHoliday ? $lastHoliday->updated_at : null;
    }

    public function refreshHolidays()
    {
        $this->emit('syncHolidays');
    }

    public function render()
    {
        return view('holidays::livewire.holiday-stats');
    }
} 