<?php

namespace App\Modules\Dashboard\Http\Livewire;

use Livewire\Component;
use App\Modules\Budgets\Models\MonthlyBudget;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RevenueChart extends Component
{
    public $chartData = [];
    public $months = [];
    public $period = '';
    public $totalRevenue = 0;
    public $totalExpenses = 0;
    public $totalTarget = 0;
    public $underBudget = 0;
    
    // For Livewire 2 compatibility
    protected $listeners = ['refreshChart' => 'loadChartData'];

    public function mount()
    {
        $this->period = '1. juli 2024 - 30. juni 2025';
        $this->loadChartData();
    }
    
    public function loadChartData()
    {
        try {
            // Get revenue data for the period July 2024 to June 2025
            $revenueData = [];
            $labels = [];
            
            Log::info('Loading budget data...');
            
            // Get budget data using Laravel's DB facade
            $budgets = DB::select("
                SELECT * FROM budgets 
                WHERE (year = 2024 AND month >= 7 AND month <= 12)
                   OR (year = 2025 AND month >= 1 AND month <= 6)
                ORDER BY year, month
            ");
            
            Log::info('Retrieved budgets:', [
                'count' => count($budgets),
                'first_budget' => isset($budgets[0]) ? json_encode($budgets[0]) : 'none'
            ]);
            
            // Calculate total statistics
            $this->totalRevenue = array_sum(array_map(function($b) { return $b->omsaetning_salg_total; }, $budgets));
            $this->totalExpenses = array_sum(array_map(function($b) { return $b->udgift_variable_kapacitet; }, $budgets));
            $this->totalTarget = array_sum(array_map(function($b) { return $b->maal_baseret_paa_udgift; }, $budgets));
            $this->underBudget = $this->totalTarget - $this->totalRevenue;
            
            Log::info('Calculated totals:', [
                'totalRevenue' => $this->totalRevenue,
                'totalExpenses' => $this->totalExpenses,
                'totalTarget' => $this->totalTarget,
                'underBudget' => $this->underBudget
            ]);
            
            // Generate complete month set including months with no data
            $this->months = [];
            
            // 2024 months (Jul-Dec)
            for ($month = 7; $month <= 12; $month++) {
                $monthDate = Carbon::createFromDate(2024, $month, 1);
                $monthName = $monthDate->locale('da')->format('M Y');
                $shortName = $monthDate->locale('da')->format('M');
                
                $this->months[] = [
                    'key' => $month . '_2024',
                    'name' => $monthName,
                    'short_name' => $shortName . ' ' . $monthDate->format('Y'),
                    'year' => 2024,
                    'month' => $month
                ];
            }
            
            // 2025 months (Jan-Jun)
            for ($month = 1; $month <= 6; $month++) {
                $monthDate = Carbon::createFromDate(2025, $month, 1);
                $monthName = $monthDate->locale('da')->format('M Y');
                $shortName = $monthDate->locale('da')->format('M');
                
                $this->months[] = [
                    'key' => $month . '_2025',
                    'name' => $monthName,
                    'short_name' => $shortName . ' ' . $monthDate->format('Y'),
                    'year' => 2025,
                    'month' => $month
                ];
            }
            
            // Fill in revenue data for each month
            foreach ($this->months as $monthData) {
                $budget = current(array_filter($budgets, function($b) use ($monthData) {
                    return $b->year == $monthData['year'] && $b->month == $monthData['month'];
                }));
                
                $revenueData[] = $budget ? (float)$budget->omsaetning_salg_total : 0;
            }
            
            Log::info('Revenue data:', [
                'data' => $revenueData
            ]);
            
            // Prepare chart data
            $this->chartData = [
                'labels' => array_map(function($m) { return $m['short_name']; }, $this->months),
                'datasets' => [
                    [
                        'label' => 'Omsætning',
                        'data' => $revenueData,
                        'backgroundColor' => 'rgba(16, 185, 129, 0.8)', // Emerald
                        'borderColor' => 'rgba(16, 185, 129, 1)',
                        'borderWidth' => 1,
                        'type' => 'bar'
                    ]
                ]
            ];
            
            Log::info('Final chart data:', [
                'chartData' => $this->chartData
            ]);
            
            // Dispatch event for both Livewire 2 and 3
            try {
                if (method_exists($this, 'dispatch')) {
                    $this->dispatch('chartDataUpdated', $this->chartData);
                }
                if (method_exists($this, 'dispatchBrowserEvent')) {
                    $this->dispatchBrowserEvent('chartDataUpdated', $this->chartData);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to dispatch chart event: ' . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            Log::error('Error loading revenue chart data: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    public function refreshChart()
    {
        $this->loadChartData();
    }
    
    public function render()
    {
        return view('dashboard::livewire.revenue-chart');
    }
} 