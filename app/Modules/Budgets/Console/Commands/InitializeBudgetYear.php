<?php

namespace App\Modules\Budgets\Console\Commands;

use App\Modules\Budgets\Models\Budget;
use Carbon\Carbon;
use Illuminate\Console\Command;

class InitializeBudgetYear extends Command
{
    protected $signature = 'budgets:init {year}';
    protected $description = 'Initialize budget records for a financial year (July-June)';

    public function handle()
    {
        $year = $this->argument('year');
        $startDate = Carbon::create($year, 7, 1);

        for ($i = 0; $i < 12; $i++) {
            $currentDate = $startDate->copy()->addMonths($i);
            
            Budget::updateOrCreate(
                [
                    'year' => $currentDate->year,
                    'month' => $currentDate->month
                ],
                [
                    'omsaetning_salg_total' => 0,
                    'udgift_variable_kapacitet' => 0,
                    'maal_baseret_paa_udgift' => 0,
                    'delmaal' => 0
                ]
            );
        }

        $nextYear = intval($year) + 1;
        $this->info("Budget records initialized for {$year}/{$nextYear}");
    }
}
