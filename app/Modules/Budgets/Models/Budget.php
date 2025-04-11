<?php

namespace App\Modules\Budgets\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Budget extends Model
{
    protected $fillable = [
        'year',
        'month',
        'omsaetning_salg_total',
        'udgift_variable_kapacitet',
        'maal_baseret_paa_udgift',
        'delmaal'
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'omsaetning_salg_total' => 'decimal:2',
        'udgift_variable_kapacitet' => 'decimal:2',
        'maal_baseret_paa_udgift' => 'decimal:2',
        'delmaal' => 'decimal:2'
    ];

    public function getMonthNameAttribute()
    {
        return Carbon::create($this->year, $this->month, 1)
            ->locale('da')
            ->isoFormat('MMMM YYYY');
    }

    public function calculateTarget()
    {
        return round($this->udgift_variable_kapacitet * 1.30, 2);
    }

    public static function createFiscalYear($year)
    {
        // Start from July of the selected year
        $startDate = Carbon::create($year, 7, 1);
        $months = [];
        
        // Create 12 months (July to June next year)
        for ($i = 0; $i < 12; $i++) {
            $currentDate = $startDate->copy()->addMonths($i);
            $months[] = self::firstOrCreate(
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
        
        return collect($months);
    }
}
