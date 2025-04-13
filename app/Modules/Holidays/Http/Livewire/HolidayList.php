<?php

namespace App\Modules\Holidays\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\Holidays\Services\HolidayService;
use App\Modules\Holidays\Models\Holiday;
use Illuminate\Support\Facades\Log;
use App\Modules\Holidays\Services\TempoService;
use Carbon\Carbon;

class HolidayList extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'year';
    public $sortDirection = 'desc';
    public $worklogs;
    public $totalEmployees = 0;
    public $totalDaysTaken = 0;
    public $averageDaysPerEmployee = 0;
    public $lastSynced = null;
    protected $holidayService;
    
    protected $queryString = ['search', 'sortField', 'sortDirection'];
    protected $listeners = ['refresh' => 'refreshHolidays'];

    public function boot()
    {
        $this->holidayService = new HolidayService();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->loadWorklogs();
        $this->calculateStatistics();
    }

    public function loadWorklogs()
    {
        try {
            $this->worklogs = $this->holidayService->getAllHolidayWorklogs()
                ->when($this->search, function($query) {
                    return $query->whereHas('employee', function($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy($this->sortField, $this->sortDirection);

            Log::info('Holiday worklogs loaded', [
                'count' => $this->worklogs->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading holiday worklogs', [
                'error' => $e->getMessage()
            ]);
            $this->worklogs = collect([]);
        }
    }

    public function calculateStatistics()
    {
        $holidays = Holiday::query()
            ->where('year', now()->year)
            ->get();

        $this->totalEmployees = $holidays->pluck('employee_id')->unique()->count();
        $this->totalDaysTaken = $holidays->sum('used_days');
        $this->averageDaysPerEmployee = $this->totalEmployees > 0 
            ? round($this->totalDaysTaken / $this->totalEmployees, 1) 
            : 0;
        
        $lastSync = Holiday::max('updated_at');
        $this->lastSynced = $lastSync ? Carbon::parse($lastSync) : null;
    }

    public function refreshHolidays()
    {
        try {
            $this->holidayService->syncHolidays();
            $this->calculateStatistics();
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Holidays synced successfully'
            ]);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Failed to sync holidays: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $holidays = Holiday::query()
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('holidays::livewire.holiday-list', [
            'holidays' => $holidays
        ]);
    }
}