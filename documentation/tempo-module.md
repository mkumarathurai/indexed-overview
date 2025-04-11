# Tempo Hours Module Documentation
The Tempo Hours Module provides a comprehensive solution for managing and viewing work hours data from Tempo within the application. This module is designed following modern modular architecture principles to ensure maintainability, scalability, and clear separation of concerns.

## Environment Setup
Required environment variables:
```
TEMPO_API_TOKEN=your_tempo_token
TEMPO_BASE_URL=https://api.eu.tempo.io/4
JIRA_BASE_URL=your_jira_url
```

[Jira API](/documentation/api/tempo-api-fields.md)
[Tempo index page design](/documentation/design/projects-module-index-page.png)
[Tempo details page design](/documentation/design/project-module-project-details-page.png)
[View Timesheet PDF](/documentation/pdf/GS-Timesheet-2025-04-06-5.pdf)

## Overview
The Tempo Hours module is responsible for tracking and displaying worked hours from Tempo API. It provides detailed insights into time tracking, invoicing-ready hours, and project-specific time management.

## Key Features
- Real-time hours tracking via Tempo API
- Historical work log analysis
- Invoice-ready hours tracking
- Project and account-specific time management
- Date range selection (current month, previous month, custom periods)
- Automatic synchronization with Tempo
- Export of time sheets as PDF
- Account and customer-based filtering

### **Tempo API Integration**
- **Worklogs**: Primary endpoint `GET https://api.eu.tempo.io/4/worklogs` for fetching time entries
- **Projects**: Use `GET https://api.eu.tempo.io/4/projects` for project data
- **Accounts**: Use `GET https://api.eu.tempo.io/4/accounts` for customer account information


Authentication uses the Tempo API token specified in environment variables.

## User Interface
The Tempo Hours module displays data in a table format with:
1. **Account** - The customer account
2. **Project Key** - The Jira project identifier
3. **Project Name** - The full project name
4. **Period Hours** - Total hours in selected period
5. **Invoice Ready** - Hours marked as ready for invoicing

Summary cards show:
- Total period hours
- Invoice-ready hours
- Internal hours
- Billable hours

Users can filter data by date ranges:
- Current month (default)
- Previous month
- Custom date range

A refresh button allows users to manually update data from the Tempo API.

## Module Overview

The Tempo Hours Module enables users to:
- View a dashboard of all projects with key metrics
- See detailed information about individual projects
- View issues associated with each project
- Generate PDF reports for projects
- Manually or automatically synchronize data with Tempo API
- Filter and sort projects by various criteria
- View monthly and invoicing hours for each project
- Navigate between different months to view historical data
- Access and analyze data from archived Jira projects and issues

Tempo Hours Module:

Purpose: Integrates specifically with the Tempo API (often used with Jira for more detailed time tracking and account/customer metadata) to fetch and analyze worklogs.
Features:
Tempo Integration: Uses a TempoApiService.php and a SyncTempoHours.php command for fetching data.
Smart Syncing: Implements different fetching strategies (Smart, Incremental, Historical) based on data recency to optimize API usage and sync time, likely managed within the sync command and potentially using Laravel's scheduler frequencies.
Hours Overview: Presents a table similar to the Projects module but potentially with different filtering (e.g., by date range using a DateRangeSelector Livewire component) and showing Tempo-specific data if available. Includes actions like view details, generate PDF, link to Jira, refresh.
PDF Export: Similar format and rules as the Projects module's PDF export, focusing on invoice-ready hours within a selected period.
Data Sources: Local Database (synced from Tempo), Tempo API.

app/Modules/TempoHours/
├── Console/
│   └── Commands/
│       └── SyncTempoHours.php
├── Controllers/
│   └── TempoHoursController.php
├── Http/
│   └── Livewire/
│       ├── TempoHoursIndex.php
│       └── TempoHourDetails.php
├── Resources/
│   └── views/
│       └── components
│       ├── tempo-hours-index.blade.php
│       └── tempo-hour-details.blade.php
├── Routes/
│   └── web.php
├── Services/
│   └── TempoHoursApiService.php
├── TempoHoursServiceProvider.php
└── README.md

## URL Structure

- `/tempo-hours`: Main projects dashboard
- `/tempo-hours/{year}/{month}`: Dashboard for specific month (e.g., /projects/2025/02)
- `/tempo-hours/{projectKey}`: Detailed view of a specific project
- `/tempo-hours/{projectKey}/download-pdf`: Generate and download a PDF report
- `/tempo-hours/refresh`: Form submission endpoint for refreshing data

### Authentication
- Uses Tempo API token authentication
- Token stored in environment variables
- Implements proper error handling for authentication failures

## Data Fetching and Display

The module implements a robust approach to data fetching and display:

1. **Tiered Data Retrieval Strategy**:
   - First tries to read from cache for optimal performance
   - If not found in cache, reads from database to avoid API rate limits
   - Only fetches from Tempo API when necessary or specifically requested
   - Progressively loads and displays data to improve user experience

2. **Database Storage**:
   - Stores project hours data by period (YYYY-MM format)
   - Records are saved in `tempo_hours` table for historical tracking
   - Stored data includes:
     - Total hours for the project in the period
     - Invoice-ready hours for tracking ready-to-invoice work
     - Last fetched timestamp to enable auto-refresh policies
   - Automatically updates database records when fetching fresh data from Tempo

3. **Month Navigation**:
   - Uses direct URL links for month selection
   - Generates URLs in the format `/tempo-hours/YYYY/MM`
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
  - Account
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

#### TempoHoursIndex
The main dashboard component that displays all projects with their current metrics:
- Worklog synchronization
- Account, Project key and name
- Monthly hours
- Invoicing-ready hours
- Action buttons for viewing details and generating PDFs
- Month selection dropdown with Danish month names
- Loading states and error handling

#### TempoHourDetails
Displays detailed information about a specific project:
- Ready for invoicing hours
- Key, Summary, Hours in a table
- Project metadata
- Hours statistics
- Account / Customer information




