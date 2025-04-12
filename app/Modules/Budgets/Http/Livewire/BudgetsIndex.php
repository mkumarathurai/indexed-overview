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
    public $notification = '';
    public $notificationType = '';

    protected $rules = [
        'budgets.*.omsaetning_salg_total' => 'nullable|integer|min:0',
        'budgets.*.udgift_variable_kapacitet' => 'nullable|integer|min:0',
    ];

    // For Livewire 2
    protected $listeners = [
        'testEvent' => 'handleTestEvent',
    ];

    // For Livewire 3
    protected function getListeners() 
    {
        return [
            'testEvent' => 'handleTestEvent',
        ];
    }

    public function mount()
    {
        Log::info('BudgetsIndex component mounted');
        $this->loadData();
    }

    public function loadData()
    {
        $this->isLoading = true;
        Log::info('Loading budgets data');

        try {
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
        } catch (\Exception $e) {
            Log::error('Error loading budget data: ' . $e->getMessage());
            session()->flash('error', 'Der opstod en fejl ved indlæsning af budgetdata: ' . $e->getMessage());
            
            // Try both methods for dispatching events to maximize compatibility
            try {
                $this->dispatch('notify', [
                    'message' => 'Fejl ved indlæsning: ' . $e->getMessage(), 
                    'type' => 'error'
                ]);
            } catch (\Exception $e) {
                Log::warning('Error dispatching notify event: ' . $e->getMessage());
            }
        }

        $this->isLoading = false;
        
        // Try both methods for dispatching events to maximize compatibility
        try {
            $this->dispatch('refresh');
        } catch (\Exception $e) {
            Log::warning('Error dispatching refresh event: ' . $e->getMessage());
        }
    }

    /**
     * Konverterer en værdi til et heltal, håndtere både standardformater og danske formater
     */
    private function convertToInteger($value)
    {
        if (empty($value)) {
            return 0;
        }
        
        // Hvis værdien er et tal, konverter direkte
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        // Fjern eventuelle tusindtalsadskillere og konverter komma til punktum
        if (is_string($value)) {
            $value = str_replace('.', '', $value); // Fjern punktummer (tusindtalsadskiller)
            $value = str_replace(',', '.', $value); // Erstat komma med punktum (decimalseparator)
        }
        
        // Konverter til decimal og derefter til heltal
        $decimal = (float) $value;
        return (int) $decimal;
    }

    public function updated($propertyName)
    {
        // Only handle updates to budget fields
        if (str_starts_with($propertyName, 'budgets.')) {
            $parts = explode('.', $propertyName);
            if (count($parts) === 3) {
                $index = $parts[1];
                $field = $parts[2];
                
                // Validate the input
                $this->validateOnly($propertyName);
                
                // Convert to integer using our custom method
                $value = $this->budgets[$index][$field];
                $this->budgets[$index][$field] = $value === '' ? 0 : $this->convertToInteger($value);
                
                Log::debug('Updated budget field', [
                    'property' => $propertyName,
                    'original_value' => $value,
                    'converted_value' => $this->budgets[$index][$field]
                ]);
            }
        }
    }

    public function handleTestEvent($data)
    {
        Log::info('Test event received', $data);
        session()->flash('success', 'Test event modtaget: ' . json_encode($data));
        session()->flash('notification', [
            'message' => 'Test event modtaget: ' . json_encode($data),
            'type' => 'success'
        ]);
    }

    public function saveBudget($year, $month)
    {
        $this->isLoading = true;

        try {
            $budget = MonthlyBudget::firstOrNew([
                'year' => $year,
                'month' => $month,
            ]);

            $revenue = (int) request()->input('omsaetning_salg_total', 0);
            $expenses = (int) request()->input('udgift_variable_kapacitet', 0);

            $budget->revenue = $revenue;
            $budget->expenses = $expenses;
            $budget->target = $budget->calculateTarget($revenue);
            $budget->sub_target = $budget->calculateSubTarget($revenue);
            $budget->diff = $budget->calculateDiff($budget->sub_target, $expenses);
            
            $budget->save();

            $this->notification = 'Budget gemt!';
            $this->notificationType = 'success';
            $this->emit('notify', $this->notification, $this->notificationType);
        } catch (\Exception $e) {
            $this->notification = 'Fejl ved gemning af budget: ' . $e->getMessage();
            $this->notificationType = 'error';
            $this->emit('notify', $this->notification, $this->notificationType);
        }

        $this->isLoading = false;
        $this->loadData();
    }

    public function destroy($year, $month)
    {
        $this->isLoading = true;

        try {
            MonthlyBudget::where('year', $year)->where('month', $month)->delete();
            
            $this->notification = 'Budget slettet!';
            $this->notificationType = 'success';
            $this->emit('notify', $this->notification, $this->notificationType);
        } catch (\Exception $e) {
            $this->notification = 'Fejl ved sletning af budget: ' . $e->getMessage();
            $this->notificationType = 'error';
            $this->emit('notify', $this->notification, $this->notificationType);
        }

        $this->isLoading = false;
        $this->loadData();
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