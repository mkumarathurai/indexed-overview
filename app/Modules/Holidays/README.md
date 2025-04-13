# Holidays Module

## Overview
The Holidays module manages employee holiday tracking, worklog synchronization with Jira, and holiday balance calculations for Indexed employees.

## Module Structure
```
app/Modules/Holidays/
├── Console/                 # Console commands
├── Database/               # Database migrations and seeders
│   ├── Migrations/        # Database structure
│   └── Seeders/          # Test data
├── Http/                   # HTTP layer
│   ├── Controllers/       # Regular Laravel controllers
│   └── Livewire/         # Livewire components
├── Models/                 # Database models
│   ├── Holiday.php       # Holiday model
│   └── HolidayWorklog.php # Holiday worklog model
├── Resources/             # Views and assets
│   └── views/            # Blade templates
│       └── livewire/     # Livewire component views
├── Routes/                # Route definitions
├── Services/             # Business logic services
├── HolidaysServiceProvider.php  # Module service provider
└── HolidaysRouteServiceProvider.php  # Route service provider
```

## Features

### 1. Holiday Management
- Track employee holiday balances
- Calculate holiday entitlements
- Monitor holiday usage
- Sync with Jira worklogs

### 2. Holiday Types
- Annual Leave
- Sick Leave
- Special Leave
- Public Holidays

### 3. Integration
- Jira Worklog Synchronization
- Employee Module Integration
- Tempo Timesheets Integration

## Models

### Holiday Model
```php
class Holiday extends Model
{
    protected $fillable = [
        'employee_id',
        'year',
        'total_days',
        'used_days',
        'remaining_days',
        'last_sync_at'
    ];

    protected $casts = [
        'last_sync_at' => 'datetime'
    ];
}
```

### HolidayWorklog Model
```php
class HolidayWorklog extends Model
{
    protected $fillable = [
        'employee_id',
        'worklog_date',
        'hours',
        'description',
        'jira_worklog_id',
        'jira_issue_id'
    ];

    protected $casts = [
        'worklog_date' => 'date'
    ];
}
```

## Database Schema

### holidays Table
```sql
CREATE TABLE holidays (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    year INT NOT NULL,
    total_days DECIMAL(5,2) NOT NULL DEFAULT 0,
    used_days DECIMAL(5,2) NOT NULL DEFAULT 0,
    remaining_days DECIMAL(5,2) NOT NULL DEFAULT 0,
    last_sync_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);
```

### holiday_worklogs Table
```sql
CREATE TABLE holiday_worklogs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    worklog_date DATE NOT NULL,
    hours DECIMAL(4,2) NOT NULL,
    description TEXT NULL,
    jira_worklog_id VARCHAR(255) NULL,
    jira_issue_id VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);
```

## Components

### Livewire Components

#### HolidayList Component
- Displays list of holiday worklogs
- Filters by date range and employee
- Real-time updates
- Export functionality

#### HolidayInfo Component
- Shows employee holiday balance
- Displays usage statistics
- Year-to-date summary

## Views

### Main Views
- `holidays/index.blade.php`: Main holiday dashboard
- `livewire/holiday-list.blade.php`: Holiday worklog listing
- `livewire/holiday-info.blade.php`: Employee holiday information

## Routes
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/holidays', [HolidaysController::class, 'index'])->name('holidays.index');
    Route::get('/holidays/{employee}', [HolidaysController::class, 'show'])->name('holidays.show');
});
```

## Services

### Holiday Calculation Service
- Calculates holiday entitlements
- Handles pro-rata calculations
- Manages holiday year transitions

### Jira Sync Service
- Syncs holiday worklogs from Jira
- Updates holiday balances
- Handles error cases and retries

## Console Commands

### Sync Holiday Worklogs
```bash
php artisan holidays:sync-worklogs
```
Syncs holiday worklogs from Jira for all employees

### Calculate Holiday Balances
```bash
php artisan holidays:calculate-balances
```
Updates holiday balances for all employees

## Configuration

### Environment Variables
```env
HOLIDAY_DAYS_PER_YEAR=25
HOLIDAY_JIRA_PROJECT=INTERNAL
HOLIDAY_ISSUE_KEY=INTERNAL-9
```

### Module Configuration
```php
return [
    'days_per_year' => env('HOLIDAY_DAYS_PER_YEAR', 25),
    'jira_project' => env('HOLIDAY_JIRA_PROJECT', 'INTERNAL'),
    'holiday_issue_key' => env('HOLIDAY_ISSUE_KEY', 'INTERNAL-9'),
];
```

## Usage Examples

### Display Holiday Information
```php
@livewire('holidays.holiday-info', ['employee' => $employee])
```

### Show Holiday Worklogs
```php
@livewire('holidays.holiday-list', [
    'employee' => $employee,
    'year' => date('Y')
])
```

## Integration with Other Modules

### Employees Module
- Holiday information displayed in employee details
- Holiday calculations based on employee start date
- Pro-rata calculations for new employees

### Projects Module
- Holiday impact on project availability
- Resource planning integration
- Workload calculations

## Security
- Authentication required for all routes
- Authorization checks in controllers
- Validation of holiday requests
- Audit logging of changes

## Best Practices
1. Always use the provided services for calculations
2. Implement proper error handling
3. Use transactions for related operations
4. Cache expensive calculations
5. Validate holiday data integrity

## Testing
```bash
php artisan test --filter=HolidayTest
```

### Test Cases
- Holiday balance calculations
- Worklog synchronization
- Pro-rata calculations
- Edge cases handling

## Maintenance

### Daily Tasks
- Sync worklogs from Jira
- Update holiday balances
- Check for anomalies

### Monthly Tasks
- Validate holiday balances
- Generate reports
- Clean up old data

### Yearly Tasks
- Carry over remaining days
- Reset holiday allowances
- Archive old records

## Support
For issues and support:
- Check the logs in `storage/logs/holidays.log`
- Contact the development team
- Refer to internal documentation

## Contributing
1. Follow the module structure
2. Add appropriate tests
3. Update documentation
4. Submit pull request

## License
Internal use only - Indexed ApS 