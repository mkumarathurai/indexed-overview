<div>
    <!-- Flash Messages -->
    <div class="container mx-auto">
        @if (session('success'))
            <div class="alert alert-success mb-6 shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
    </div>

    <!-- Statistics Cards -->
    @include('budgets::partials.stats-cards', [
        'totalRevenue' => $totalRevenue,
        'totalExpenses' => $totalExpenses,
        'totalTarget' => $totalTarget,
        'underBudget' => $underBudget
    ])

    <!-- Budget Cards Grid -->
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($budgets as $index => $budget)
                <div class="bg-white rounded-xl shadow-md p-4 border border-gray-100">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ $budget['month_name'] }} {{ $budget['year'] }}</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Omsætning</label>
                            <div class="mt-1">
                                <input type="number" 
                                    wire:model.live="budgets.{{ $index }}.omsaetning_salg_total"
                                    wire:change="saveBudget({{ $budget['year'] }}, {{ $budget['month'] }})"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    step="1000">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Udgift</label>
                            <div class="mt-1">
                                <input type="number"
                                    wire:model.live="budgets.{{ $index }}.udgift_variable_kapacitet"
                                    wire:change="saveBudget({{ $budget['year'] }}, {{ $budget['month'] }})"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    step="1000">
                            </div>
                        </div>

                        <div class="pt-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Forventet:</span>
                                <span class="font-medium">{{ number_format($budget['maal_baseret_paa_udgift'] ?? 0, 0, ',', '.') }} kr.</span>
                            </div>
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-gray-500">Delmål:</span>
                                <span class="font-medium">{{ number_format($budget['delmaal'] ?? 0, 0, ',', '.') }} kr.</span>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end space-x-2">
                            <button wire:click="saveBudget({{ $budget['year'] }}, {{ $budget['month'] }})"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Gem
                            </button>
                            @if($budget['omsaetning_salg_total'] > 0)
                                <button wire:click="destroy({{ $budget['id'] }})"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Slet
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
