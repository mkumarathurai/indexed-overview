<?php

namespace App\Modules\Budgets\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Budgets\Models\MonthlyBudget;
use Illuminate\Support\Facades\Log;

class BudgetsController extends Controller
{
    public function index()
    {
        return view('budgets::budgets-index');
    }
    
    public function save(Request $request)
    {
        Log::info('Saving budget', $request->all());
        
        try {
            $year = (int)$request->input('year');
            $month = (int)$request->input('month');
            $omsaetning = $this->convertToInteger($request->input('omsaetning_salg_total'));
            $udgift = $this->convertToInteger($request->input('udgift_variable_kapacitet'));
            
            // Calculate target based on expenses (30% profit)
            $maal = (int)round($udgift * 1.30);
            
            // Get all previous months
            $previousMonths = MonthlyBudget::where(function($query) use ($year, $month) {
                $query->where('year', '<', $year)
                    ->orWhere(function($q) use ($year, $month) {
                        $q->where('year', '=', $year)
                            ->where('month', '<', $month);
                    });
            })->orderBy('year')->orderBy('month')->get();
            
            // Calculate accumulated difference
            $accumulatedDiff = 0;
            foreach ($previousMonths as $prevBudget) {
                $prevOmsaetning = $prevBudget->omsaetning_salg_total;
                $prevMaal = $prevBudget->maal_baseret_paa_udgift;
                $accumulatedDiff += ($prevOmsaetning - $prevMaal);
            }
            
            // Calculate delmål
            $delmaal = (int)round($accumulatedDiff + ($omsaetning - $maal));
            
            // Save the budget using updateOrCreate
            $budget = MonthlyBudget::updateOrCreate(
                ['year' => $year, 'month' => $month],
                [
                    'omsaetning_salg_total' => $omsaetning,
                    'udgift_variable_kapacitet' => $udgift,
                    'maal_baseret_paa_udgift' => $maal,
                    'delmaal' => $delmaal
                ]
            );
            
            return redirect()->route('budgets.index')->with('success', 'Budgettet blev gemt');
        } catch (\Exception $e) {
            Log::error('Error saving budget: ' . $e->getMessage());
            return redirect()->route('budgets.index')->with('error', 'Der opstod en fejl: ' . $e->getMessage());
        }
    }
    
    public function delete(Request $request)
    {
        Log::info('Deleting budget', $request->all());
        
        try {
            $year = (int)$request->input('year');
            $month = (int)$request->input('month');
            
            // Find and delete the budget
            $deleted = MonthlyBudget::where('year', $year)
                ->where('month', $month)
                ->delete();
            
            if ($deleted) {
                return redirect()->route('budgets.index')->with('success', 'Budgettet blev slettet');
            } else {
                return redirect()->route('budgets.index')->with('info', 'Intet budget fundet at slette');
            }
        } catch (\Exception $e) {
            Log::error('Error deleting budget: ' . $e->getMessage());
            return redirect()->route('budgets.index')->with('error', 'Der opstod en fejl: ' . $e->getMessage());
        }
    }
    
    /**
     * Konverterer en værdi til et heltal, håndterer både standardformater og danske formater
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
} 