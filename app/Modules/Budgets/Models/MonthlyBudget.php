<?php

namespace App\Modules\Budgets\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyBudget extends Model
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

    // Calculate target based on expenses (30% profit)
    public function calculateTarget()
    {
        return $this->udgift_variable_kapacitet * 1.30;
    }

    // Calculate remaining target
    public function calculateRemainingTarget()
    {
        return $this->maal_baseret_paa_udgift - $this->omsaetning_salg_total;
    }

    // Get month name
    public function getMonthNameAttribute()
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    // Get year-month combination
    public function getYearMonthAttribute()
    {
        return $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT);
    }

    // Scope to get budgets for a specific year
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    // Scope to get budgets for a specific month
    public function scopeForMonth($query, $month)
    {
        return $query->where('month', $month);
    }
}