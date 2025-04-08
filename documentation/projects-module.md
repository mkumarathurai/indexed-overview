# Projects Module Documentation
The Projects Module provides a comprehensive solution for managing and viewing Jira project data within the application. This module is designed following modern modular architecture principles to ensure maintainability, scalability, and clear separation of concerns.

## Environment Setup
Required environment variables:
```

PROJECT_NOTIFICATION_EMAIL=your-email@example.com
JIRA_API_TOKEN=your_jira_token
JIRA_BASE_URL=your_jira_url
```


[Jira API](/documentation/api/jira-api-fields.md)
[Project index page design](/documentation/design/projects-module-index-page.png)
[Project details page design](/documentation/design/project-module-project-details-page.png)
[View Timesheet PDF](/documentation/pdf/GS-Timesheet-2025-04-06-5.pdf)



## Module Overview

The Projects Module enables users to:
- View a dashboard of all projects with key metrics
- See detailed information about individual projects
- View issues associated with each project
- Generate PDF reports for projects
- Manually or automatically synchronize data with Jira
- Filter and sort projects by various criteria
- View monthly and invoicing hours for each project
- Navigate between different months to view historical data
- Access and analyze data from archived Jira projects and issues


## Directory Structure

```
app/Modules/Projects/
├── Console/
│   └── Commands/
│       └── SyncProjects.php
├── Controllers/
│   └── ProjectsController.php
├── Http/
│   └── Livewire/
│       ├── ProjectsIndex.php
│       └── ProjectDetails.php
├── Resources/
│   └── views/
│       └── components/
│           ├── projects-index.blade.php
│           └── project-details.blade.php
├── Routes/
│   └── web.php
├── Services/
│   └── ProjectsApiService.php
├── ProjectsServiceProvider.php
└── README.md
```

## URL Structure

- `/projects`: Main projects dashboard
- `/projects/{year}/{month}`: Dashboard for specific month (e.g., /projects/2025/02)
- `/projects/{projectKey}`: Detailed view of a specific project
- `/projects/{projectKey}/download-pdf`: Generate and download a PDF report
- `/projects/refresh`: Form submission endpoint for refreshing data

## API Integration

https://developer.atlassian.com/cloud/jira/platform/rest/v3/api-group-projects/#api-rest-api-3-project-search-get


### Jira API Endpoints Used
- `/rest/api/3/project/search`: Fetch project details (with `includeArchived=true` for archived projects)
- `/rest/api/3/search`: Search for issues (with `includeArchivedProjects=true` parameter)
- `/rest/api/3/worklog`: Retrieve worklog data
- `/rest/api/3/issue/{issueIdOrKey}`: Get specific issue details

### Authentication
- Uses Jira API token authentication
- Token stored in environment variables
- Implements proper error handling for authentication failures

## Archived Projects and Tasks Support

The module fully supports fetching and analyzing data from archived Jira projects and tasks:

1. **Project Search**:
   - API calls include the `includeArchived=true` parameter to fetch all projects, including archived ones.
   - Archived projects are visually marked in the interface.

2. **Task Search**:
   - API calls include the `includeArchivedProjects=true` parameter to fetch tasks from archived projects.
   - Historical worklog data is preserved for analysis.

3. **Data Retrieval**:
   - Archived projects' and tasks' worklogs are available for historical analysis.
   - Hours are calculated correctly from archived data.
   - Reports can include data from both active and archived projects and tasks.

4. **Database Storage**:
   - Archived data is stored in the same structure as active data, ensuring consistency.
   - The `project_hours` table tracks hours for both active and archived projects.

5. **UI Display**:
   - Archived projects and tasks are visually distinguished in the UI.
   - Filters allow users to include or exclude archived data in views and reports.

## Data Fetching and Display

The module implements a robust approach to data fetching and display:

1. **Tiered Data Retrieval Strategy**:
   - First tries to read from cache for optimal performance
   - If not found in cache, reads from database to avoid API rate limits
   - Only fetches from Jira API when necessary or specifically requested
   - Progressively loads and displays data to improve user experience

2. **Database Storage**:
   - Stores project hours data by period (YYYY-MM format)
   - Records are saved in `project_hours` table for historical tracking
   - Stored data includes:
     - Total hours for the project in the period
     - Invoice-ready hours for tracking ready-to-invoice work
     - Last fetched timestamp to enable auto-refresh policies
   - Automatically updates database records when fetching fresh data from Jira

3. **Month Navigation**:
   - Uses direct URL links for month selection
   - Generates URLs in the format `/projects/YYYY/MM`
   - Supports navigation via dropdown menu with Danish month names
   - Maintains proper state when switching between months
   - Includes data validation to prevent accessing invalid date ranges

## Caching Strategy

The module implements intelligent caching to optimize performance:
- Project lists are cached for 360 minutes
- Individual project data is cached separately
- Worklog data is cached to reduce API calls
- The `--clear-cache` option on the sync command allows for forced refreshes
- Cache keys are structured by project and date range

## Status Handling

The module specifically handles the "Ready for Invoicing" status:
1. **Case-Insensitive Matching**: 
   - Uses case-insensitive comparison for status names
   - Handles variations like "Ready for Invoicing", "Ready for invoicing", etc.
   - Implements case-insensitive JQL queries using the `~` operator

2. **Hours Calculation**:
   - Calculates hours only for issues with "Ready for Invoicing" status
   - Aggregates worklog timeSpentSeconds into hours
   - Rounds hours to 2 decimal places for display

3. **Data Display**:
   - Shows only "Ready for Invoicing" issues in the project details view
   - Displays individual issue hours and total project hours
   - Updates in real-time when issues change status

## UI Components

### Dashboard Features
- Month selection dropdown with Danish localization and direct URL navigation
- Post-based refresh button with server-side processing
- Statistics cards showing:
  - Total Projects
  - Monthly Hours
  - Invoicing Hours
- Sortable project table
- Loading states and error handling
- Responsive design with Tailwind CSS

### Project Table
- Sortable columns:
  - Project ID
  - Project Name
  - Monthly Hours
  - Invoicing Hours
- Action buttons for:
  - View project details
  - Download PDF
  - Refresh button for getting the Invoice hours for the actual project
- Loading indicators for async data
- Empty state handling


## Key Components

### 1. Livewire Components

#### ProjectsIndex
The main dashboard component that displays all projects with their current metrics:
- Worklog synchronization
- Project key and name
- Monthly hours
- Invoicing-ready hours
- Action buttons for viewing details and generating PDFs
- Month selection dropdown with Danish month names
- Loading states and error handling

#### ProjectDetails
Displays detailed information about a specific project:
- Ready for invoicing hours
- Key, Summary, Hours in a table
- Project metadata
- Hours statistics

### 2. API Services

#### ProjectsApiService
Manages communication with the Jira API:
- Fetches and caches project data
- Retrieves project details and issues
- Handles error states and logging
- Implements case-insensitive status matching for "Ready for Invoicing" issues
- Calculates monthly hours for projects
- Processes worklog data
- Implements batch processing for multiple projects
- Updates project hours and invoice-ready hours in the database

Key Methods:
```php
getProjects($forceRefresh = false) // Fetches all projects from Jira with option to force refresh
getProjectHours($projectKey, $startDate, $endDate) // Gets hours for a specific project within a date range
getBatchMonthlyHours($projectKeys, $startDate, $endDate) // Gets hours for multiple projects within a date range
getBatchInvoiceReadyHours($projectKeys) // Gets invoice-ready hours for multiple projects
```

#### JiraService
Handles direct communication with Jira API:
- Manages authentication and API requests
- Implements caching for API responses
- Calculates hours for issues with specific statuses
- Uses case-insensitive JQL queries for status matching
- Handles worklog data retrieval and processing with efficient pagination
- Includes archived projects and issues in all API requests
- Processes worklog data from both active and archived projects
- Returns both total hours and invoice-ready hours for projects

Key Methods:
```php
getHoursForCurrentMonth($projectKey, $startDate, $endDate, $invoiceReadyOnly = false) // Gets hours for a project within a date range
```

### 3. Controllers

#### ProjectsController
Manages the web interface and data flow:
- Handles route parameters and request data
- Processes month selection and navigation
- Manages project data retrieval and display
- Handles error states and user feedback
- Provides data for the Livewire components
- Parses period strings into proper date ranges for API calls

Key Methods:
```php
index($year = null, $month = null) // Main dashboard view with month navigation
show($projectKey) // Project details view
downloadPdf($projectKey) // PDF report generation
refresh(Request $request) // Handles refresh requests and updates database records
```

### 4. Console Commands

#### SyncProjects
Command-line utility for synchronizing project data:
```bash
php artisan projects:sync
```

Options:
- `--clear-cache`: Clears the cache before syncing

The command is scheduled to run daily at 2:00 AM to ensure data is kept up-to-date.

#### Sync Project Worklogs
```bash
php artisan projects:sync-worklogs {year?} {--clear-cache}
```

This command synchronizes worklogs for all projects for a specific year:

Options:
- `year`: The year to sync worklogs for (defaults to current year)
- `--clear-cache`: Clear the cache before syncing

Example:
```bash
# Sync worklogs for 2024
php artisan projects:sync-worklogs 2024

# Sync worklogs for 2024 and clear cache
php artisan projects:sync-worklogs 2024 --clear-cache
```

The command will:
1. Get all projects
2. For each project:
   - Get worklogs for each month of the specified year
   - Save worklogs to the database
   - Show progress with a progress bar
3. Log any errors that occur during the process

#### Fetch Archived Jira Data
```bash
php artisan jira:fetch-archived {--project=} {--start-date=} {--end-date=} {--force}
```

This command fetches worklog data specifically from archived Jira projects and issues:

Options:
- `--project`: Specific project key to fetch (optional)
- `--start-date`: Start date in Y-m-d format (default: 1 year ago)
- `--end-date`: End date in Y-m-d format (default: today)
- `--force`: Force fetch all data even if previously fetched

Example:
```bash
# Fetch all archived projects and their worklogs from the past year
php artisan jira:fetch-archived

# Fetch archived data for a specific project
php artisan jira:fetch-archived --project=PROJ

# Fetch archived data for a specific date range
php artisan jira:fetch-archived --start-date=2022-01-01 --end-date=2022-12-31
```

The command:
1. Fetches all projects or a specific project
2. Identifies archived projects in the results
3. Uses JQL queries to find issues with worklogs in the specified date range
4. Processes and stores all worklog records
5. Provides detailed progress information and metrics

#### Sync Historical Jira Data
```bash
php artisan jira:sync-historical {--start-year=2023} {--end-year=2024} {--force}
```

This command synchronizes historical data from all Jira projects, including archived ones:

Options:
- `--start-year`: Start year to sync from (default: 2023)
- `--end-year`: End year to sync to (default: 2024)
- `--force`: Force refresh all data

Example:
```bash
# Sync historical data for default years
php artisan jira:sync-historical

# Sync historical data for a specific range
php artisan jira:sync-historical --start-year=2020 --end-year=2023

# Force refresh all historical data
php artisan jira:sync-historical --force
```

The command:
1. Fetches all projects including archived ones
2. For each project and each month in the specified year range:
   - Retrieves worklog data
   - Calculates and stores hours information
   - Shows progress information
3. Creates historical records for analysis and reporting


## Troubleshooting

### Common Issues

1. **Missing Data**: If project data is missing, try:
   ```bash
   php artisan projects:sync --clear-cache
   ```

2. **Missing Archived Data**: If archived project data is missing:
   ```bash
   php artisan jira:fetch-archived --force
   ```

3. **No Data for a Specific Month**: If a specific month shows no data:
   ```bash
   php artisan jira:fetch-archived --start-date=YYYY-MM-01 --end-date=YYYY-MM-31 --force
   ```

4. **Refresh Button Not Working**: If the refresh button is not working:
   - Check that the route `projects.refresh` is properly defined in the routes file
   - Clear route cache with `php artisan route:clear`
   - Verify that the refresh form includes the correct period in a hidden input
   - Check if dates are correctly parsed from period string (format YYYY-MM) to date range
   - Confirm that the `project_hours` table has the `invoice_ready_hours` column
   - Check the server logs for any errors during the refresh process

5. **Month Selection Not Working**: If month selection dropdown isn't working:
   - Ensure the dropdown items are using direct URL links instead of JavaScript events
   - Verify the URLs are correctly formatted as `/projects/YYYY/MM`
   - Clear the application and view caches

6. **API Errors**: If you encounter API errors, check:
   - Jira API credentials in `.env`
   - Laravel logs at `storage/logs/laravel.log`
   - Projects sync logs at `storage/logs/projects-sync.log`

7. **Performance Issues**: If the dashboard is loading slowly:
   - Check for heavy API traffic
   - Verify caching is working correctly
   - Consider optimizing the `getIssuesForProject` method if many issues exist

## Future Enhancements

Planned enhancements for the Projects Module include:

1. Advanced filtering and sorting options
2. Project comparison features
3. Team member assignment visualization
4. Integration with additional Jira data points
5. Custom dashboards for different user roles
6. Export functionality for project data
7. Real-time updates using Jira webhooks
8. Enhanced PDF report customization
9. Team performance metrics 

## Recent Updates and Considerations

### Recent Changes

1. **Fixed ProjectsApiService Data Structure**:
   - Updated `getBatchMonthlyHours($projectKeys, $startDate, $endDate)` to properly handle date ranges
   - The method now returns a more detailed data structure with nested attributes including `totalHours` and `invoiceReadyHours` 
   - Data is updated directly in the database during retrieval, ensuring data consistency

2. **Database Schema Updates**:
   - Added `invoice_ready_hours` column to the `project_hours` table
   - This allows for separate tracking of both total hours and invoice-ready hours
   - Database operations use `updateOrInsert` to prevent duplicate entries

3. **Controller and View Integration**:
   - Modified `ProjectsController` to parse period strings (YYYY-MM) into proper date ranges
   - Updated data access in templates to accommodate the new nested structure
   - Fixed template inheritance with proper `@yield('content')` in the layout file

4. **Debugging and Troubleshooting**:
   - Added detailed logging throughout the data retrieval process
   - Created scripts to diagnose and fix issues with project hours calculation
   - Improved error handling in API service methods

### Layout System

The Projects Module uses a two-layer layout system:

1. **Main Layout** (`projects.blade.php`):
   - Provides the base HTML structure and common assets
   - Uses `@yield('content')` to place content from child views
   - Includes Livewire scripts and styles

2. **Page Templates** (`pages/index.blade.php`):
   - Extend the main layout using `@extends('projects::layouts.projects')`
   - Define content sections using `@section('content')` and `@endsection`
   - Include Livewire components with parameter passing

3. **Component Templates** (`components/projects-index.blade.php`):
   - Implement the actual UI and functionality
   - Receive data from the controller via Livewire component

### Template Inheritance Flow

The data flow between controller and views follows this pattern:

1. `ProjectsController::index()` retrieves and processes data
2. Data is passed to `pages/index.blade.php` template
3. Template extends the layout and includes the Livewire component
4. Livewire component receives and displays the data

### Common Issues and Solutions

1. **View Not Found Errors**:
   - Ensure view paths are correctly namespaced with `projects::`
   - Verify that all directories in the view path exist
   - Clear view cache with `php artisan view:clear` after creating new views

2. **Undefined Variable Errors**:
   - Check that all variables passed from controller to view are defined
   - Use the `??` null coalescing operator to provide defaults
   - In Blade templates, use `@isset()` or `$variable ?? 'default'` to handle missing variables

3. **Data Type Mismatches**:
   - Be careful with nested data structures returned from API services
   - Use proper accessors like `$monthlyHours[$projectKey]['totalHours']` instead of `$monthlyHours[$projectKey]`
   - When summing collections, ensure all elements are scalar values

4. **Layout Issues**:
   - Make sure layouts use `@yield('content')` instead of component-style `$slot`
   - Child views must use `@section('content')` and `@endsection`
   - Clear view cache after making changes to layout structure

### Future Development Guidelines

When extending the Projects Module:

1. **New API Methods**:
   - Follow the established pattern with detailed return types
   - Include proper error handling and logging
   - Update database records as part of the data retrieval process

2. **UI Enhancements**:
   - Build on the existing component structure
   - Use the same layout inheritance pattern
   - Maintain consistent error handling and loading states

3. **Data Structure Changes**:
   - Update all accessing code when modifying data structures
   - Provide fallbacks for backward compatibility
   - Document new data structures in method PHPDoc comments



## Projects Module

### Purpose
Manages project information synced from Jira, focusing on time tracking relevant for invoicing.


#### Features
- **Jira Integration**  
  Synchronizes project details and worklogs via the Jira API using a dedicated service (`ProjectsApiService.php`) and a scheduled command (`SyncProjects.php`).

- **Project Overview**  
  Displays a table (`<x-table>`) listing projects with key metrics:
    - Project ID
    - Project name
    - Total Monthly Hours  
    - Invoice-ready Hours  
  Includes actions per project:
    - View details  
    - Generate PDF  
    - Link to Jira  

- **Project Details View**  
    - Shows detailed time registrations for a specific project with tasks and worklogs

- **PDF Export**  
  Generates a timesheet PDF:  
  `Timesheet - {projectKey} - {createdDate}.pdf`  
  Contains only tasks marked _"Ready for invoicing"_.  
  Includes:
  - Project info  
  - Task breakdown (ID, Name, Hours)  
  - Invoice period  
  - Generation date

- **Data Sources**  
  - Local Database (synced from Jira)  
  - Jira API

