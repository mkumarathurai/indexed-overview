# Budgets Module Documentation

The Budgets Module provides a comprehensive solution for managing monthly budgets and financial tracking within the application. This module is designed following modern modular architecture principles to ensure maintainability, scalability, and clear separation of concerns.

## Module Overview

The Budgets Module enables users to:
- View and manage monthly budgets for the financial year (July-June)
- Track revenue and expenses
- Calculate targets based on expenses (30% profit margin)
- Monitor financial performance
- Generate financial reports
- Handle Danish currency formatting

## Key Components

### 1. Livewire Components

#### BudgetsIndex
The main dashboard component that displays all budgets with their current metrics:
- Monthly revenue tracking
- Variable capacity expenses
- Target calculations
- Sub-target tracking
- Real-time updates
- Danish month names
- Loading states and error handling

### 2. Models

#### MonthlyBudget
Manages budget data and calculations:
- Revenue tracking
- Expense management
- Target calculations
- Financial year handling
- Data validation and formatting

Key Methods:
```php
calculateTarget() // Calculates target based on expenses (30% profit)
calculateRemainingTarget() // Calculates remaining target
getMonthNameAttribute() // Gets localized month name
getYearMonthAttribute() // Gets year-month combination
```

### 3. Controllers

#### BudgetsController
Manages budget operations:
- CRUD operations for budgets
- Data validation
- Danish number format handling
- Target calculations
- Response formatting

Key Methods:
```php
store() // Creates or updates budget data
destroy() // Deletes budget data
```

## Directory Structure

```
app/Modules/Budgets/
├── Console/
│   └── Commands/
├── Controllers/
│   └── BudgetsController.php
├── Http/
│   └── Livewire/
│       └── BudgetsIndex.php
├── Models/
│   └── MonthlyBudget.php
├── Resources/
│   └── views/
│       └── components/
│           └── budgets-index.blade.php
├── Routes/
│   └── web.php
├── BudgetsServiceProvider.php
└── BudgetsRouteServiceProvider.php
```

## URL Structure

- `/budgets`: Main budgets dashboard
- `/budgets` (POST): Store new budget data
- `/budgets/{budget}` (DELETE): Delete budget data

## Data Structure

### MonthlyBudget Model
```php
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
```

# Budget Calculations Documentation

## Overview
This document explains the budget calculations used in the Monthly Budget module, specifically how the target (mål) and difference (delmål) are calculated.

## Calculations

### 1. Target (Mål baseret på udgift)
The target is calculated as expenses plus 30% profit:
```
mål = udgift * 1.30
```

Example:
- If expenses (udgift) = 302.000
- Target (mål) = 302.000 * 1.30 = 392.600

### 2. Difference (Delmål)
The difference is calculated as the accumulated difference from all previous months plus the current month's difference:

```php
// For each month:
current_diff = omsaetning - mål
accumulated_diff = sum of all previous months' (omsaetning - mål)
delmål = accumulated_diff + current_diff
```

## Real Examples from Excel Sheet

### July 2024
- Omsætning: 201.000
- Udgift: 341.000
- Mål: 341.000 * 1.30 = 443.300
- Delmål: 201.000 - 443.300 = -242.300 (first month, no accumulation)

### August 2024
- Omsætning: 395.000
- Udgift: 302.000
- Mål: 302.000 * 1.30 = 392.600
- Previous accumulated: -242.300
- Current diff: 395.000 - 392.600 = 2.400
- Delmål: -242.300 + 2.400 = -239.900

### September 2024
- Omsætning: 536.000
- Udgift: 329.000
- Mål: 329.000 * 1.30 = 427.700
- Previous accumulated: -239.900
- Current diff: 536.000 - 427.700 = 108.300
- Delmål: -239.900 + 108.300 = -131.600

### October 2024
- Omsætning: 562.000
- Udgift: 353.000
- Mål: 353.000 * 1.30 = 458.900
- Previous accumulated: -131.600
- Current diff: 562.000 - 458.900 = 103.100
- Delmål: -131.600 + 103.100 = -28.500

## Code Implementation
```php
public function saveBudget($year, $month)
{
    // Find the current budget index
    $index = collect($this->budgets)->search(function($budget) use ($year, $month) {
        return $budget['year'] == $year && $budget['month'] == $month;
    });

    $budget = $this->budgets[$index];
    
    // Get basic values
    $omsaetning = (float) $budget['omsaetning_salg_total'];
    $udgift = (float) $budget['udgift_variable_kapacitet'];

    // Calculate target (30% profit)
    $maal = round($udgift * 1.30);
    
    // Calculate accumulated difference from previous months
    $previousMonths = collect($this->budgets)->take($index);
    $accumulatedDiff = $previousMonths->sum(function($b) {
        $prevOmsaetning = (float) $b['omsaetning_salg_total'];
        $prevUdgift = (float) $b['udgift_variable_kapacitet'];
        $prevMaal = round($prevUdgift * 1.30);
        return $prevOmsaetning - $prevMaal;
    });
    
    // Calculate final difference
    $delmaal = round($accumulatedDiff + ($omsaetning - $maal));
}
```

## Important Notes
1. All calculations use whole numbers (no decimals)
2. The delmål (difference) accumulates from month to month
3. Each month's delmål includes:
   - The accumulated difference from all previous months
   - Plus the current month's difference between revenue and target
4. A negative delmål means we are behind target
5. A positive delmål means we are ahead of target 

## Features

### 1. Financial Year Handling
- Financial year runs from July to June
- Automatic month generation for the current financial year
- Danish month names and formatting

### 2. Target Calculations
- Target based on expenses (30% profit margin)
- Automatic calculation of sub-targets
- Real-time updates of calculations

### 3. Data Formatting
- Danish number format handling
- Currency formatting in DKK
- Proper decimal handling

### 4. User Interface
- Clean, responsive design
- Real-time updates
- Loading states
- Error handling
- Success messages

## Integration Points

### 1. Dashboard Integration
- Provides revenue data for dashboard
- Used in financial reporting
- Integrated with project hours tracking

### 2. Currency Handling
- Uses Laravel's Number facade for formatting
- Handles Danish currency format
- Proper decimal and thousand separators

## Troubleshooting

### Common Issues

1. **Data Formatting**: If numbers aren't displaying correctly:
   - Check Danish locale settings
   - Verify number format conversion
   - Ensure proper decimal handling

2. **Target Calculations**: If targets aren't calculating correctly:
   - Verify expense values
   - Check profit margin calculation
   - Ensure proper data types

3. **Financial Year**: If months aren't displaying correctly:
   - Check current date
   - Verify financial year calculation
   - Ensure proper month ordering

## Future Enhancements

Planned enhancements for the Budgets Module include:

1. Advanced financial reporting
2. Budget comparison features
3. Export functionality
4. Historical data analysis
5. Budget templates
6. Automated budget calculations
7. Integration with accounting software
8. Custom financial year settings
9. Budget alerts and notifications
10. Team budget management 