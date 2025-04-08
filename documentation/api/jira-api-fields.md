# Jira API Fields Available for Module Development

This document outlines the various data fields and entities available through the Jira API that can be utilized for building new modules in our application.

## Core Entities

### 1. Projects

Projects are the top-level organizational containers in Jira.

```json
{
  "id": "10000",
  "key": "PROJ",
  "name": "Sample Project",
  "description": "Description of the project",
  "lead": {
    "key": "username",
    "name": "User Name",
    "displayName": "User Display Name",
    "active": true
  },
  "url": "https://company.atlassian.net/browse/PROJ",
  "projectCategory": {
    "id": "10000",
    "name": "Category name",
    "description": "Category description"
  },
  "archived": false,
  "components": [
    {
      "id": "10000",
      "name": "Component 1",
      "description": "This is component 1"
    }
  ]
}
```

### 2. Issues

Issues are the basic work items in Jira (tasks, bugs, stories, etc.)

```json
{
  "id": "10000",
  "key": "PROJ-123",
  "self": "https://company.atlassian.net/rest/api/3/issue/10000",
  "fields": {
    "summary": "Issue summary",
    "description": {
      "type": "doc",
      "content": []
    },
    "issuetype": {
      "id": "10000",
      "name": "Bug",
      "description": "A problem which impairs or prevents functionality"
    },
    "project": {
      "id": "10000",
      "key": "PROJ",
      "name": "Sample Project"
    },
    "priority": {
      "id": "3",
      "name": "Medium"
    },
    "status": {
      "id": "10000",
      "name": "To Do",
      "statusCategory": {
        "id": 2,
        "key": "new",
        "name": "To Do"
      }
    },
    "creator": {
      "accountId": "account-id",
      "displayName": "User Name"
    },
    "assignee": {
      "accountId": "account-id",
      "displayName": "User Name"
    },
    "reporter": {
      "accountId": "account-id",
      "displayName": "User Name"
    },
    "created": "2023-01-01T12:00:00.000+0000",
    "updated": "2023-01-02T12:00:00.000+0000",
    "duedate": "2023-02-01",
    "resolution": {
      "id": "10000",
      "name": "Done"
    },
    "components": [
      {
        "id": "10000",
        "name": "Component 1"
      }
    ],
    "labels": ["label1", "label2"],
    "timetracking": {
      "originalEstimate": "4h",
      "remainingEstimate": "2h",
      "timeSpent": "2h",
      "originalEstimateSeconds": 14400,
      "remainingEstimateSeconds": 7200,
      "timeSpentSeconds": 7200
    },
    "watches": {
      "watchCount": 5,
      "isWatching": true
    },
    "subtasks": [
      {
        "id": "10001",
        "key": "PROJ-124",
        "fields": {
          "summary": "Subtask Summary"
        }
      }
    ],
    "fixVersions": [
      {
        "id": "10000",
        "name": "1.0",
        "released": false,
        "releaseDate": "2023-05-01"
      }
    ],
    "customfield_10000": "Custom field value"
  }
}
```

### 3. Worklogs

Worklogs track time spent on issues.

```json
{
  "id": "100028",
  "self": "https://company.atlassian.net/rest/api/3/issue/10000/worklog/100028",
  "author": {
    "accountId": "account-id",
    "displayName": "User Name"
  },
  "updateAuthor": {
    "accountId": "account-id",
    "displayName": "User Name"
  },
  "created": "2023-01-01T12:00:00.000+0000",
  "updated": "2023-01-01T12:00:00.000+0000",
  "started": "2023-01-01T09:00:00.000+0000",
  "timeSpent": "3h",
  "timeSpentSeconds": 10800,
  "comment": {
    "type": "doc",
    "content": []
  },
  "issueId": "10000",
  "issueKey": "PROJ-123"
}
```

### 4. Users

User information available through Jira API.

```json
{
  "accountId": "account-id",
  "displayName": "User Name",
  "emailAddress": "user@example.com",
  "active": true,
  "timeZone": "Europe/Copenhagen",
  "avatar": {
    "16x16": "https://avatar-url",
    "24x24": "https://avatar-url",
    "32x32": "https://avatar-url",
    "48x48": "https://avatar-url"
  }
}
```

### 5. Status Information

Statuses represent the state of an issue within a workflow.

```json
{
  "id": "10000",
  "name": "Ready for invoicing",
  "description": "This issue is ready to be invoiced",
  "statusCategory": {
    "id": 3,
    "key": "done",
    "name": "Done"
  }
}
```

## Common Fields Used in Our Application

These are the most commonly used fields in our current implementation:

| Field | Description | Entity | Example Value |
|-------|-------------|--------|--------------|
| `key` | The project or issue key | Project, Issue | "PROJ", "PROJ-123" |
| `name` | Name of the entity | Project, Status, Priority | "Sample Project" |
| `summary` | Brief description of the issue | Issue | "Implement feature X" |
| `status.name` | Current status of the issue | Issue | "Ready for invoicing" |
| `timeSpentSeconds` | Time spent on the issue in seconds | Worklog | 7200 |
| `started` | When the work began | Worklog | "2023-01-01T09:00:00.000+0000" |
| `description` | Detailed description | Project, Issue | "This is a project for..." |
| `fields.priority.name` | Issue priority | Issue | "High", "Medium", "Low" |
| `fields.assignee.displayName` | Person assigned to issue | Issue | "John Doe" |
| `fields.reporter.displayName` | Person who reported the issue | Issue | "Jane Smith" |
| `fields.created` | Creation timestamp | Issue | "2023-01-01T12:00:00.000+0000" |
| `fields.updated` | Last update timestamp | Issue | "2023-01-02T12:00:00.000+0000" |

## How Time Tracking Is Structured

In our application, we track:

1. **Total Hours**: All recorded hours for a project or issue.
2. **Invoice Ready Hours**: Hours associated with issues having the status "Ready for invoicing".

The time is stored in two ways:
- In Jira, as `timeSpentSeconds` in the worklog entries.
- In our application, converted to hours by dividing by 3600.

## Making Jira API Requests

Our application uses the following endpoints:

1. **Get Projects**: `GET /rest/api/3/project/search`
2. **Get Issues for Project**: `GET /rest/api/3/search` with JQL `project = 'PROJECT_KEY'`
3. **Get Issue Details**: `GET /rest/api/3/issue/{issueIdOrKey}`
4. **Get Issue Worklogs**: `GET /rest/api/3/issue/{issueIdOrKey}/worklog`
5. **Search Issues**: `GET /rest/api/3/search` with custom JQL

## JQL (Jira Query Language)

JQL is used to query issues with specific criteria. Examples:

```
project = 'PROJ' AND status = 'Ready for invoicing'
project = 'PROJ' AND worklogDate >= '2023-01-01' AND worklogDate <= '2023-01-31'
project = 'PROJ' AND assignee = currentUser()
```

## Custom Fields

Jira allows for custom fields which appear in the API as `customfield_XXXXX`. The meaning of these fields depends on your Jira instance configuration.

## Tips for Building New Modules

1. **Use caching**: Jira API calls can be slow and have rate limits.
2. **Filter on the server**: Use JQL to filter data on Jira's side rather than fetching everything.
3. **Error handling**: Properly handle API errors and timeouts.
4. **Authentication**: Use Basic Auth with API tokens as implemented in our `JiraService`.
5. **Data transformation**: Convert Jira's data structure to a simpler format for your module.

## Example: Fetching Invoice-Ready Issues

```php
// Get all issues with "Ready for invoicing" status for a project
$jql = "project = '{$projectKey}' AND status = 'Ready for invoicing'";
$response = $jiraService->searchIssues($jql);

// Calculate total invoice-ready hours
$invoiceReadyHours = 0;
foreach ($response['issues'] as $issue) {
    $issueKey = $issue['key'];
    $seconds = DB::table('worklogs')
        ->where('issue_key', $issueKey)
        ->sum('time_spent_seconds');
        
    $issueHours = $seconds / 3600; // Convert seconds to hours
    $invoiceReadyHours += $issueHours;
}
``` 