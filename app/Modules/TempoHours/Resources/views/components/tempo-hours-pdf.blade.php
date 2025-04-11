<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tempo Hours Report - {{ $project->project_key }} - {{ $period }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .project-info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .summary {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tempo Hours Report</h1>
        <h2>{{ $project->project_key }} - {{ $period }}</h2>
    </div>

    <div class="project-info">
        <h3>Project Information</h3>
        <p><strong>Project Key:</strong> {{ $project->project_key }}</p>
        <p><strong>Period:</strong> {{ $period }}</p>
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <table>
            <tr>
                <th>Total Hours</th>
                <td>{{ number_format($project->period_hours / 3600, 1) }}</td>
            </tr>
            <tr>
                <th>Invoice Ready Hours</th>
                <td>{{ number_format($project->invoice_ready_hours / 3600, 1) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html> 