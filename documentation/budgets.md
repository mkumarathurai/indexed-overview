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
save() // Creates or updates budget data
delete() // Deletes budget data by year and month
convertToInteger() // Converts input values to integers, handling Danish number formats
```

## Directory Structure

```
app/Modules/Budgets/
├── Console/
│   └── Commands/
├── Http/
│   ├── Controllers/
│   │   └── BudgetsController.php
│   └── Livewire/
│       └── BudgetsIndex.php
├── Models/
│   └── MonthlyBudget.php
├── Resources/
│   └── views/
│       ├── budgets-index.blade.php
│       └── livewire/
│           └── budgets-index.blade.php
├── Routes/
│   └── web.php
├── Database/
│   └── Migrations/
├── BudgetsServiceProvider.php
└── BudgetsRouteServiceProvider.php
```

## URL Structure

- `/budgets`: Main budgets dashboard
- `/budgets/save` (POST): Store new budget data
- `/budgets/delete` (DELETE): Delete budget data

## Implementation Details

### Form Handling

The module uses two approaches for handling budget data:

1. **Traditional HTML Forms**:
   - Forms submit to the BudgetsController for direct HTTP operations
   - Year and month are used as unique identifiers for budgets
   - Data validation and Danish number formats are handled server-side

2. **Livewire Component Methods**:
   - Provides real-time calculations and updates
   - Handles loading states and notifications
   - Used for enhanced user experience with reactive data

### Number Formatting

The application handles Danish number formatting in both directions:

- **Input**: Accepts Danish number format (with comma as decimal separator and period as thousand separator)
- **Output**: Displays numbers in Danish format using either:
  - Laravel's Number facade for currency display
  - Custom number_format with Danish locale settings

### Budget Calculations

#### Target Calculation
The target is calculated as expenses plus 30% profit:
```
mål = udgift * 1.30
```

#### Difference (Delmål) Calculation
The difference is calculated as the accumulated difference from all previous months plus the current month's difference:

```php
// For each month:
current_diff = omsaetning - mål
accumulated_diff = sum of all previous months' (omsaetning - mål)
delmål = accumulated_diff + current_diff
```

### Error Handling

The module implements comprehensive error handling:
- Exception catching for all database operations
- Detailed logging of errors
- User-friendly notifications
- Flash messages for both success and error states

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

## Important Notes

1. All calculations use whole numbers (no decimals)
2. The delmål (difference) accumulates from month to month
3. Each month's delmål includes:
   - The accumulated difference from all previous months
   - Plus the current month's difference between revenue and target
4. A negative delmål means we are behind target
5. A positive delmål means we are ahead of target

## UI Components

### 1. Statistics Cards
- Total Revenue: Displays total revenue across all budgets
- Total Expenses: Shows total expenses for all budgets
- Total Target: Displays the total target amount
- Under Budget: Shows how much current revenue is under the target

### 2. Budget Table
- Displays budgets by month with editable fields for revenue and expenses
- Shows calculated targets and sub-targets
- Provides save and delete actions for each budget entry
- Handles both numeric inputs and formatted Danish values

### 3. Notifications
- Toast notifications for successful operations and errors
- Flash messages for form submissions
- Real-time feedback for user actions

## Troubleshooting

### Common Issues

1. **Data Formatting**: If numbers aren't displaying correctly:
   - Check Danish locale settings
   - Verify number format conversion in convertToInteger() method
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