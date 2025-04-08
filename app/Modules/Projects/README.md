# Projects Module Documentation

The Projects Module provides a comprehensive solution for managing and viewing Jira project data within the application. This module is designed following modern modular architecture principles to ensure maintainability, scalability, and clear separation of concerns.

## Features

- View a dashboard of all projects with key metrics
- See detailed information about individual projects
- View issues associated with each project
- Generate PDF reports for projects
- Manually or automatically synchronize data with Jira
- Filter and sort projects by various criteria
- View monthly and invoicing hours for each project
- Navigate between different months to view historical data
- Access and analyze data from archived Jira projects and issues

## Environment Setup

Required environment variables:

```
PROJECT_NOTIFICATION_EMAIL=your-email@example.com
JIRA_API_TOKEN=your_jira_token
JIRA_BASE_URL=your_jira_url
```

## Directory Structure

```
Projects
├── Console
│   └── Commands
│       └── SyncProjects.php
├── Controllers
│   └── ProjectsController.php
├── Http
│   └── Livewire
│       ├── ProjectsIndex.php
│       └── ProjectDetails.php
├── Resources
│   └── views
│       └── components
│           ├── projects-index.blade.php
│           └── project-details.blade.php
├── Routes
│   └── web.php
├── Services
│   └── ProjectsApiService.php
├── ProjectsServiceProvider.php
└── README.md
```

## Installation

1. Clone the repository.
2. Install dependencies using Composer.
3. Set up the required environment variables in your `.env` file.
4. Run the database migrations if necessary.

## Usage

- Access the main projects dashboard at `/projects`.
- Use the provided commands to synchronize project data with Jira.
- Utilize the Livewire components for real-time updates and interactions.

## Future Enhancements

- Advanced filtering and sorting options
- Project comparison features
- Team member assignment visualization
- Integration with additional Jira data points
- Custom dashboards for different user roles
- Export functionality for project data
- Real-time updates using Jira webhooks
- Enhanced PDF report customization
- Team performance metrics 

This README serves as a guide to understanding and utilizing the Projects Module effectively.