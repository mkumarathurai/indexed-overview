<?php

namespace App\Modules\Budgets\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Budgets\Models\Budget;
use Carbon\Carbon;

class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $budgetData = [
            // July 2024
            ['month' => 7, 'year' => 2024, 'omsaetning_salg_total' => 201000, 'udgift_variable_kapacitet' => 341000],
            // August 2024
            ['month' => 8, 'year' => 2024, 'omsaetning_salg_total' => 395000, 'udgift_variable_kapacitet' => 302000],
            // September 2024
            ['month' => 9, 'year' => 2024, 'omsaetning_salg_total' => 536000, 'udgift_variable_kapacitet' => 329000],
            // October 2024
            ['month' => 10, 'year' => 2024, 'omsaetning_salg_total' => 562000, 'udgift_variable_kapacitet' => 353000],
            // November 2024
            ['month' => 11, 'year' => 2024, 'omsaetning_salg_total' => 698000, 'udgift_variable_kapacitet' => 369000],
            // December 2024
            ['month' => 12, 'year' => 2024, 'omsaetning_salg_total' => 386000, 'udgift_variable_kapacitet' => 324000],
            // January 2025
            ['month' => 1, 'year' => 2025, 'omsaetning_salg_total' => 456000, 'udgift_variable_kapacitet' => 336000],
            // February 2025
            ['month' => 2, 'year' => 2025, 'omsaetning_salg_total' => 416000, 'udgift_variable_kapacitet' => 336000],
            // March 2025
            ['month' => 3, 'year' => 2025, 'omsaetning_salg_total' => 442000, 'udgift_variable_kapacitet' => 350000],
            // April 2025
            ['month' => 4, 'year' => 2025, 'omsaetning_salg_total' => 0, 'udgift_variable_kapacitet' => 370000],
            // May 2025
            ['month' => 5, 'year' => 2025, 'omsaetning_salg_total' => 0, 'udgift_variable_kapacitet' => 370000],
            // June 2025
            ['month' => 6, 'year' => 2025, 'omsaetning_salg_total' => 0, 'udgift_variable_kapacitet' => 370000],
        ];

        foreach ($budgetData as $data) {
            $budget = Budget::firstOrNew([
                'year' => $data['year'],
                'month' => $data['month']
            ]);

            $budget->omsaetning_salg_total = $data['omsaetning_salg_total'];
            $budget->udgift_variable_kapacitet = $data['udgift_variable_kapacitet'];
            $budget->maal_baseret_paa_udgift = $budget->calculateTarget();
            $budget->save();
        }
    }
} 