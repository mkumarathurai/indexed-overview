<!-- filepath: /Projects/Projects/Resources/views/components/project-details.blade.php -->
<div>
    <h1>{{ $project->name }}</h1>
    <p><strong>Key:</strong> {{ $project->key }}</p>
    <p><strong>Summary:</strong> {{ $project->summary }}</p>
    
    <h2>Hours Statistics</h2>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Hours</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Hours</td>
                <td>{{ $project->total_hours }}</td>
            </tr>
            <tr>
                <td>Ready for Invoicing</td>
                <td>{{ $project->invoice_ready_hours }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Worklog Details</h2>
    <ul>
        @foreach($project->worklogs as $worklog)
            <li>{{ $worklog->date }}: {{ $worklog->hours }} hours - {{ $worklog->description }}</li>
        @endforeach
    </ul>

    <button wire:click="downloadPdf">Download PDF</button>
</div>