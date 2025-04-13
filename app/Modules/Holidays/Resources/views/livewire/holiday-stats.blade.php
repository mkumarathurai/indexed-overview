<div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="p-5">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Holiday Statistics</h3>
            <button wire:click="refreshHolidays" class="text-indigo-600 hover:text-indigo-900">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
        
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Employees</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalEmployees }}</dd>
            </div>

            <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Days Taken</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalDaysTaken }}</dd>
            </div>

            <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Average Days/Employee</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $averageDaysPerEmployee }}</dd>
            </div>
        </dl>

        @if($lastSynced)
            <p class="mt-4 text-sm text-gray-500">Last updated: {{ $lastSynced->diffForHumans() }}</p>
        @endif
    </div>
</div> 