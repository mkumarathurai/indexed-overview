<div>
    <div class="bg-white shadow-sm rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Timer for {{ $projectKey }}</h2>
            <div>
                <button wire:click="$emit('downloadPdf')" class="btn-secondary mr-2">
                    Download PDF
                </button>
                <button wire:click="loadWorklogs" class="btn-primary">
                    <span wire:loading.remove>Opdater</span>
                    <span wire:loading>Opdaterer...</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th>Dato</th>
                        <th>Issue</th>
                        <th>Beskrivelse</th>
                        <th>Timer</th>
                        <th>Fakturérbar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($worklogs as $worklog)
                        <tr>
                            <td>{{ $worklog->started_at->format('d/m/Y') }}</td>
                            <td>{{ $worklog->issue_key }}</td>
                            <td>{{ $worklog->description }}</td>
                            <td>{{ number_format($worklog->time_spent_seconds / 3600, 2) }}</td>
                            <td>{{ $worklog->is_invoice_ready ? 'Ja' : 'Nej' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
