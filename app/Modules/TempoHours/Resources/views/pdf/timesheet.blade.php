<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Timesheet - {{ $projectKey }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.4;
            margin: 0;
            padding: 40px;
            color: #333;
            font-size: 12px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: normal;
        }
        .project-info {
            margin-bottom: 40px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            font-weight: normal;
            background-color: #fafafa;
            color: #666;
        }
        .text-right {
            text-align: right;
        }
        .total {
            font-weight: normal;
            border-top: 2px solid #eee;
        }
        .total td {
            padding-top: 10px;
        }
        .meta-info {
            margin-top: 15px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Timeregistrering</h1>
    
    <div class="project-info">
        <p><strong>Projekt:</strong> {{ $projectKey }}</p>
        <p><strong>Periode:</strong> {{ Carbon\Carbon::createFromFormat('Y-m', $period)->translatedFormat('F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Dato</th>
                <th>Issue</th>
                <th>Beskrivelse</th>
                <th class="text-right">Timer</th>
                <th>Fakturerbar</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalHours = 0;
                $billableHours = 0;
            @endphp
            @foreach($worklogs as $worklog)
                @php
                    $hours = $worklog->time_spent_seconds / 3600;
                    $totalHours += $hours;
                    if ($worklog->is_invoice_ready) {
                        $billableHours += $hours;
                    }
                @endphp
                <tr>
                    <td>{{ $worklog->started_at->format('d/m/Y') }}</td>
                    <td>{{ $worklog->issue_key }}</td>
                    <td>{{ $worklog->description }}</td>
                    <td class="text-right">{{ number_format($hours, 2) }}</td>
                    <td>{{ $worklog->is_invoice_ready ? 'Ja' : 'Nej' }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="3" class="text-right">I alt:</td>
                <td class="text-right">{{ number_format($totalHours, 2) }}</td>
                <td></td>
            </tr>
            <tr class="total">
                <td colspan="3" class="text-right">Fakturerbare timer:</td>
                <td class="text-right">{{ number_format($billableHours, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="meta-info">
        <p>Genereret: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Kun opgaver markeret som "Fakturerbar" tæller med i fakturerbare timer</p>
    </div>
</body>
</html>
