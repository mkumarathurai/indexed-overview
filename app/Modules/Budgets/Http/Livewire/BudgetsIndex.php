<?php

namespace App\Modules\Budgets\Http\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Modules\Budgets\Models\MonthlyBudget;
use Illuminate\Support\Facades\Log;

class BudgetsIndex extends Component
{
    public $months = [];
    public $budgets = [];
    public $isLoading = false;
    public $totalRevenue = 0;
    public $totalExpenses = 0;
    public $totalTarget = 0;
    public $underBudget = 0;

    public function mount()
    {
        Log::info('BudgetsIndex component mounted');
        $this->loadData();
    }

    public function loadData()
    {
        $this->isLoading = true;
        Log::info('Loading budgets data');

        // Get all months from July 2024 to June 2025
        $this->months = [];
        for ($month = 7; $month <= 12; $month++) {
            $this->months[] = [
                'month' => $month,
                'year' => 2024,
                'name' => Carbon::createFromDate(2024, $month, 1)->locale('da')->isoFormat('MMMM')
            ];
        }
        for ($month = 1; $month <= 6; $month++) {
            $this->months[] = [
                'month' => $month,
                'year' => 2025,
                'name' => Carbon::createFromDate(2025, $month, 1)->locale('da')->isoFormat('MMMM')
            ];
        }

        // Get budgets for both 2024 and 2025
        $budgets = MonthlyBudget::whereIn('year', [2024, 2025])
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        Log::info('Found ' . $budgets->count() . ' budgets in database');

        // Initialize budgets array with default values
        $this->budgets = [];
        foreach ($this->months as $monthData) {
            $budget = $budgets->first(function($b) use ($monthData) {
                return $b->year == $monthData['year'] && $b->month == $monthData['month'];
            });

            $this->budgets[] = [
                'id' => $budget ? $budget->id : null,
                'year' => $monthData['year'],
                'month' => $monthData['month'],
                'month_name' => $monthData['name'],
                'omsaetning_salg_total' => $budget ? $budget->omsaetning_salg_total : 0,
                'udgift_variable_kapacitet' => $budget ? $budget->udgift_variable_kapacitet : 0,
                'maal_baseret_paa_udgift' => $budget ? $budget->maal_baseret_paa_udgift : 0,
                'delmaal' => $budget ? $budget->delmaal : 0,
            ];
        }

        // Calculate totals for statistics cards
        $this->totalRevenue = $budgets->sum('omsaetning_salg_total');
        $this->totalExpenses = $budgets->sum('udgift_variable_kapacitet');
        $this->totalTarget = $budgets->sum('maal_baseret_paa_udgift');
        $this->underBudget = $this->totalTarget - $this->totalRevenue;

        Log::info('Totals calculated:', [
            'revenue' => $this->totalRevenue,
            'expenses' => $this->totalExpenses,
            'target' => $this->totalTarget
        ]);

        $this->isLoading = false;
    }

    private function convertDanishNumber($value)
    {
        if (is_string($value)) {
            // Remove any currency symbols and spaces
            $value = trim(str_replace(['kr', 'DKK', ' '], '', $value));

            // Remove thousand separators (dots)
            $value = str_replace('.', '', $value);
            
            // Replace comma with dot for decimal point
            $value = str_replace(',', '.', $value);
            
            return (float) $value;
        }
        
        return (float) $value;
    }

    public function saveBudget($year, $month)
    {
        $this->isLoading = true;
        Log::info('Saving budget for ' . $year . '-' . $month);
        
        // Find index by matching both year and month
        $index = collect($this->budgets)->search(function($budget) use ($year, $month) {
            return $budget['year'] == $year && $budget['month'] == $month;
        });

        if ($index === false) {
            Log::error('Could not find budget index for year ' . $year . ' and month ' . $month);
            $this->isLoading = false;
            return;
        }

        try {
            $budget = $this->budgets[$index];
            Log::info('Budget data:', $budget);
            
            // Convert string values to integers (øre)
            $omsaetning = (int) ($this->convertDanishNumber($budget['omsaetning_salg_total']) * 100);
            $udgift = (int) ($this->convertDanishNumber($budget['udgift_variable_kapacitet']) * 100);

            // Calculate target based on expenses (30% profit)
            $maal = (int) round($udgift * 1.30);
            
            // Calculate delmaal as the accumulated difference from previous months
            $previousMonths = collect($this->budgets)->take($index);
            $accumulatedDiff = $previousMonths->sum(function($b) {
                $prevOmsaetning = (int) ($this->convertDanishNumber($b['omsaetning_salg_total']) * 100);
                $prevUdgift = (int) ($this->convertDanishNumber($b['udgift_variable_kapacitet']) * 100);
                $prevMaal = (int) round($prevUdgift * 1.30);
                return $prevOmsaetning - $prevMaal;
            });
            
            $delmaal = (int) round($accumulatedDiff + ($omsaetning - $maal));

            Log::info('Calculations:', [
                'udgift' => $udgift,
                'profit_multiplier' => 1.30,
                'calculated_maal' => $maal,
                'omsaetning' => $omsaetning,
                'accumulated_diff' => $accumulatedDiff,
                'current_diff' => $omsaetning - $maal,
                'calculated_delmaal' => $delmaal
            ]);

            // Convert back to kroner before saving
            $savedBudget = MonthlyBudget::updateOrCreate(
                [
                    'year' => $year,
                    'month' => $month
                ],
                [
                    'omsaetning_salg_total' => $omsaetning / 100,
                    'udgift_variable_kapacitet' => $udgift / 100,
                    'maal_baseret_paa_udgift' => $maal / 100,
                    'delmaal' => $delmaal / 100
                ]
            );

            Log::info('Saved budget:', $savedBudget->toArray());
            session()->flash('success', 'Budget gemt');
        } catch (\Exception $e) {
            Log::error('Error saving budget: ' . $e->getMessage());
            session()->flash('error', 'Der opstod en fejl ved gemning af budget: ' . $e->getMessage());
        }

        $this->loadData();
        $this->isLoading = false;
    }

    public function destroy($year, $month)
    {
        $this->isLoading = true;
        Log::info('Deleting budget for ' . $year . '-' . $month);
        
        try {
            MonthlyBudget::where('year', $year)
                ->where('month', $month)
                ->delete();
            
            session()->flash('success', 'Budget slettet');
        } catch (\Exception $e) {
            Log::error('Error deleting budget: ' . $e->getMessage());
            session()->flash('error', 'Der opstod en fejl ved sletning af budget: ' . $e->getMessage());
        }

        $this->loadData();
        $this->isLoading = false;
    }

    public function render()
    {
        try {
            return view('budgets::livewire.budgets-index', [
                'months' => $this->months,
                'budgets' => $this->budgets,
                'totalRevenue' => $this->totalRevenue,
                'totalExpenses' => $this->totalExpenses,
                'totalTarget' => $this->totalTarget,
                'underBudget' => $this->underBudget
            ]);
        } catch (\Exception $e) {
            Log::error('Error rendering BudgetsIndex component: ' . $e->getMessage());
            return view('budgets::error', ['error' => $e->getMessage()]);
        }
    }
}