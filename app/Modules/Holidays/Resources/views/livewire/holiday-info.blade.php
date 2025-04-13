<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">Holiday Information</h3>
    </div>

    <div class="mt-4">
        <dl class="divide-y divide-gray-200">
            <div class="py-4 flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Total Days</dt>
                <dd class="text-sm text-gray-900">{{ $employee->holiday->total_days ?? 'N/A' }}</dd>
            </div>
            <div class="py-4 flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Used Days</dt>
                <dd class="text-sm text-gray-900">{{ $employee->holiday->used_days ?? 'N/A' }}</dd>
            </div>
            <div class="py-4 flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Remaining Days</dt>
                <dd class="text-sm text-gray-900">{{ $employee->holiday->remaining_days ?? 'N/A' }}</dd>
            </div>
            <div class="py-4 flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>

            </div>
        </dl>
    </div>
</div> 