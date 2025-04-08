# Tempo API Fields Available for Module Development

This document outlines the various data fields and entities available through the Tempo API that can be utilized for building new modules in our application.

## Core Entities

### 1. Tempo Projects

Projects represent billable work initiatives in Tempo, often mapped to Jira projects.

```json
{
  "tempo_id": 12345,
  "key": "PROJ",
  "name": "Sample Project",
  "status": "ACTIVE",
  "projectType": "BILLABLE",
  "projectCategory": "CLIENT_PROJECT",
  "startDate": "2023-01-01",
  "endDate": "2023-12-31",
  "customer": {
    "id": 67890,
    "name": "Client Company",
    "key": "CLIENT"
  },
  "lead": {
    "accountId": "user-account-id",
    "displayName": "User Name"
  }
}
```

### 2. Tempo Worklogs

Worklogs in Tempo track time spent with additional financial/billing information.

```json
{
  "tempoWorklogId": 12345,
  "jiraWorklogId": 67890,
  "issue": {
    "id": "10001",
    "key": "PROJ-123",
    "summary": "Issue summary"
  },
  "timeSpentSeconds": 3600,
  "billableSeconds": 3600,
  "startDate": "2023-01-15",
  "startTime": "09:00:00",
  "description": "Working on feature implementation",
  "author": {
    "accountId": "user-account-id",
    "displayName": "User Name"
  },
  "attributes": {
    "billable": true
  }
}
```

### 3. Tempo Customers/Accounts

Customers in Tempo represent the clients for whom billable work is performed.

```json
{
  "tempo_id": 12345,
  "key": "CLIENT",
  "name": "Client Company",
  "accountId": "client-account-id",
  "status": "ACTIVE",
  "contactPerson": "Contact Name",
  "contactEmail": "contact@client.com"
}
```

### 4. Tempo Monthly Hours

This entity represents aggregate time data for a project during a specific month.

```json
{
  "tempo_project_id": 12345,
  "tempo_customer_id": 67890,
  "year": 2023,
  "month": 1,
  "total_hours": 160.5,
  "billable_hours": 152.0
}
```

## Fields Used in Our Application

These are the most commonly used fields in our current implementation:

| Field | Description | Entity | Example Value |
|-------|-------------|--------|--------------|
| `tempo_id` | Unique identifier in Tempo | Project, Customer | 12345 |
| `key` | Project key (matches Jira project key) | Project | "PROJ" |
| `name` | Name of the entity | Project, Customer | "Sample Project" |
| `status` | Current status | Project, Customer | "ACTIVE" |
| `tempoWorklogId` | Unique identifier for a worklog | Worklog | 12345 |
| `timeSpentSeconds` | Time spent in seconds | Worklog | 3600 |
| `billableSeconds` | Billable time in seconds | Worklog | 3600 |
| `startDate` | Date the work was performed | Worklog | "2023-01-15" |
| `hours_spent` | Time spent in hours (converted from seconds) | Worklog | 1.0 |
| `billable_hours` | Billable time in hours (converted from seconds) | Worklog | 1.0 |
| `total_hours` | Total hours for a month | Monthly Hours | 160.5 |
| `billable_hours` | Billable hours for a month | Monthly Hours | 152.0 |

## How Time Tracking Is Structured in Tempo

Tempo extends Jira's time tracking capabilities with:

1. **Billable vs. Non-billable Time**: Time can be marked as billable or non-billable.
2. **Customer Association**: Time is associated with customers/clients.
3. **Financial Tracking**: Supports billing rates, cost rates, and revenue tracking.
4. **Monthly Summaries**: Aggregates hours spent by month for reporting.

The time is stored in two ways:
- In Tempo API, as `timeSpentSeconds` and `billableSeconds` in worklog entries.
- In our application, converted to hours by dividing by 3600.

## Database Model Relationships

Our Tempo data models have the following relationships:

1. **TempoCustomer** → **TempoProject** (one-to-many)
2. **TempoProject** → **TempoWorklog** (one-to-many)
3. **TempoProject** → **TempoMonthlyHour** (one-to-many)

## Making Tempo API Requests

Our application uses the following endpoints:

1. **Get Customers**: `GET /tempo-accounts/1/account`
2. **Get Projects**: `GET /tempo-accounts/1/account/{accountId}/project`
3. **Get Worklogs**: `GET /tempo-timesheets/4/worklogs`
4. **Get Worklogs for Project**: `GET /tempo-timesheets/4/worklogs/project/{projectId}`
5. **Get Worklogs for Date Range**: `GET /tempo-timesheets/4/worklogs?dateFrom={yyyy-mm-dd}&dateTo={yyyy-mm-dd}`

## Query Parameters for Tempo API

The Tempo API supports various query parameters:

```
dateFrom=2023-01-01&dateTo=2023-01-31
projectId=12345
limit=1000
offset=0
```

## Custom Attributes in Tempo

Tempo supports custom attributes for worklogs which can be used for additional tracking:

```json
"attributes": {
  "billable": true,
  "customField1": "value1",
  "customField2": "value2"
}
```

## Tips for Building New Modules with Tempo API

1. **Work with local data**: Fetch data from Tempo API and store in local models to reduce API calls.
2. **Use time periods wisely**: Tempo API performance can degrade with large date ranges.
3. **Handle rate limits**: Tempo API has rate limits that need to be respected.
4. **Time Unit Conversions**: Always convert between seconds (API) and hours (application) appropriately.
5. **Missing Data**: Be prepared to handle projects or issues that might not exist in Tempo but do exist in Jira.

## Example: Fetching Monthly Hours for a Project

```php
// Get the TempoProject by key
$tempoProject = TempoProject::where('key', $projectKey)->first();

if ($tempoProject) {
    // Get the month's start and end dates
    $startDate = "{$year}-{$month}-01";
    $endDate = date('Y-m-t', strtotime($startDate));
    
    // Get worklogs from the database
    $worklogs = TempoWorklog::where('tempo_project_id', $tempoProject->id)
        ->whereBetween('work_date', [$startDate, $endDate])
        ->get();
    
    // Calculate total and billable hours
    $totalHours = $worklogs->sum('hours_spent');
    $billableHours = $worklogs->sum('billable_hours');
    
    return [
        'total_hours' => $totalHours,
        'billable_hours' => $billableHours,
        'worklog_count' => $worklogs->count()
    ];
}
```

## Importing Data from Tempo API

Our application regularly syncs data from Tempo API to our local database models:

1. **Customers**: Imported into `TempoCustomer` model
2. **Projects**: Imported into `TempoProject` model
3. **Worklogs**: Imported into `TempoWorklog` model
4. **Monthly Summaries**: Calculated and stored in `TempoMonthlyHour` model

This synchronization ensures we have local access to all the Tempo data needed for reporting and analytics without making excessive API calls.

## Data Transformation and Storage

When importing from Tempo API, we:

1. Convert time from seconds to hours 
2. Map Tempo identifiers to our internal identifiers
3. Save the raw API response for reference
4. Calculate derived values like monthly totals

## Key Differences from Jira API

While Jira API focuses on work management (issues, statuses, workflows), Tempo API focuses on:

1. Time tracking with financial dimensions
2. Customer/client relationships
3. Billable vs. non-billable work
4. Project billing and financial reporting

Understanding these differences is key to successfully using Tempo API data in your modules. 