<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
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
        .project-id {
            margin-bottom: 40px;
            color: #666;
            white-space: nowrap;

        }
        .section {
            margin-top: 30px;
        }
        h2 {
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: normal;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
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
            border-top: 2px solid #000;
        }
        .total td {
            padding-top: 10px;
            white-space: nowrap;
        }
        .meta-info {
            margin-top: 15px;
            font-size: 10px;
            color: #666;
        }
        .meta-info div {
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Timesheet</h1>
        <div class="project-info">
            <p><strong>Project:</strong> {{ $project['key'] }} - {{ $project['name'] }}</p>
            <p><strong>Generated:</strong> {{ $generatedAt->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <table>
            <thead>
                <tr>
                <th style="width: 15%">ID</th>
                <th style="width: 70%">Opgave</th>
                <th style="width: 15%" class="hours text-right">Timer</th>
            </tr>

            </thead>
        <tbody>
            @foreach($issues as $issue)
                <tr>
                    <td>{{ $issue['key'] }}</td>
                    <td>{{ $issue['fields']['summary'] }}</td>
                     <td class="text-right">{{ number_format($issue['hours'], 2) }}</td>
                </tr>
            @endforeach

                            <tr class="total">
                    <td></td>
                    <td class="text-right">I alt:</td>
                    <td class="text-right">{{ number_format($totalHours, 2) }} timer</td>
                </tr>

        </tbody>
    </table>

        <div class="meta-info">
            <div>Genereret: {{ $generatedAt }}</div>
            <div>Faktureringsperiode: {{ $billingPeriod['start'] }} - {{ $billingPeriod['end'] }}</div>
        </div>

    <div class="footer">
        <p>Only includes tasks marked as "Ready for invoicing"</p>
    </div>
</body>
</html>
