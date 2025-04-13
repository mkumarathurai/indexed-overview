<div class="container mx-auto px-4 py-8" wire:poll.30s id="budgets-container">
        <!-- 
        <div class="flex gap-3">
            <button onclick="showNotification('Test notifikation', 'info')"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Test Notification
            </button>
            <form wire:submit.prevent="handleTestEvent({message: 'Test via form submit'})" class="inline-block">
                <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Test Event (Form)
                </button>
            </form>
            <form wire:submit.prevent="mount" class="inline-block">
                <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    Reload
                </button>
            </form>
            <button onclick="console.log('Debug button clicked', {year: 2024, month: 7}); console.log('Livewire object:', typeof Livewire !== 'undefined' ? Livewire : 'Not defined');"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                Console Debug
            </button>
        </div>
    </div>
 --> 

    <!-- Toast notification -->
    <div id="notification" 
         style="display: none;"
         class="fixed bottom-4 right-4 z-50 p-4 rounded-lg shadow-lg bg-blue-100 border-l-4 border-blue-500 text-blue-700">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg id="notification-icon-success" style="display: none;" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <svg id="notification-icon-error" style="display: none;" class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <svg id="notification-icon-info" style="display: none;" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p id="notification-message" class="text-sm font-medium"></p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button onclick="hideNotification()" class="inline-flex rounded-md p-1.5 bg-blue-50 text-blue-500 hover:bg-blue-100">
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div>
        <!-- Flash Messages -->
        <div class="container mx-auto mb-6">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-md" role="alert">
                    <div class="flex">
                        <div class="py-1">
                            <svg class="h-6 w-6 text-green-500 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold">Success!</p>
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-md" role="alert">
                    <div class="flex">
                        <div class="py-1">
                            <svg class="h-6 w-6 text-red-500 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold">Fejl!</p>
                            <p>{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            @if (session('info'))
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4 rounded shadow-md" role="alert">
                    <div class="flex">
                        <div class="py-1">
                            <svg class="h-6 w-6 text-blue-500 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold">Information</p>
                            <p>{{ session('info') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

       <!-- Budget Table -->
        <div class="flex flex-col">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Total omsætning</dt>
                        <dd class="mt-1 text-2xl font-semibold text-emerald-600">{{ number_format($totalRevenue, 0, ',', '.') }} kr</dd>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Total udgifter</dt>
                        <dd class="mt-1 text-2xl font-semibold text-red-600">{{ number_format($totalExpenses, 0, ',', '.') }} kr</dd>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Total mål</dt>
                        <dd class="mt-1 text-2xl font-semibold text-indigo-600">{{ number_format($totalTarget, 0, ',', '.') }} kr</dd>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Under budget</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($underBudget, 0, ',', '.') }} kr</dd>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">År</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Måned</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Omsætning</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Udgifter</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mål</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delmål</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Handlinger</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($budgets as $index => $budget)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $budget['year'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $budget['month'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <input type="text" 
                                            name="omsaetning_{{ $budget['year'] }}_{{ $budget['month'] }}"
                                            id="omsaetning_{{ $budget['year'] }}_{{ $budget['month'] }}"
                                            value="{{ $budget['omsaetning_salg_total'] }}"
                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-right w-32"
                                            placeholder="0"
                                            onchange="document.querySelector('input[name=\'omsaetning_salg_total\'][form=\'save-form-{{ $budget['year'] }}-{{ $budget['month'] }}\']').value = this.value">
                                        @error('budgets.' . $index . '.omsaetning_salg_total') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <input type="text" 
                                            name="udgift_{{ $budget['year'] }}_{{ $budget['month'] }}"
                                            id="udgift_{{ $budget['year'] }}_{{ $budget['month'] }}"
                                            value="{{ $budget['udgift_variable_kapacitet'] }}"
                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-right w-32"
                                            placeholder="0"
                                            onchange="document.querySelector('input[name=\'udgift_variable_kapacitet\'][form=\'save-form-{{ $budget['year'] }}-{{ $budget['month'] }}\']').value = this.value">
                                        @error('budgets.' . $index . '.udgift_variable_kapacitet') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($budget['maal_baseret_paa_udgift'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($budget['delmaal'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <!-- Standard html form uden Livewire direktiver -->
                                        <form method="POST" action="/budgets/save" id="save-form-{{ $budget['year'] }}-{{ $budget['month'] }}">
                                            @csrf
                                            <input type="hidden" name="year" value="{{ $budget['year'] }}">
                                            <input type="hidden" name="month" value="{{ $budget['month'] }}">
                                            <input type="hidden" name="omsaetning_salg_total" value="{{ $budget['omsaetning_salg_total'] }}" form="save-form-{{ $budget['year'] }}-{{ $budget['month'] }}">
                                            <input type="hidden" name="udgift_variable_kapacitet" value="{{ $budget['udgift_variable_kapacitet'] }}" form="save-form-{{ $budget['year'] }}-{{ $budget['month'] }}">
                                            <button type="submit" 
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Gem
                                            </button>
                                        </form>
                                        
                                        <!-- Standard html form for sletning -->
                                        <form method="POST" action="/budgets/delete" class="inline-block mt-2" id="delete-form-{{ $budget['year'] }}-{{ $budget['month'] }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="year" value="{{ $budget['year'] }}">
                                            <input type="hidden" name="month" value="{{ $budget['month'] }}">
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 ml-2">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Slet
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Notifikationsfunktioner
    function showNotification(message, type) {
        // Nulstil alle ikoner
        document.getElementById('notification-icon-success').style.display = 'none';
        document.getElementById('notification-icon-error').style.display = 'none';
        document.getElementById('notification-icon-info').style.display = 'none';
        
        // Vis det relevante ikon
        document.getElementById('notification-icon-' + type).style.display = 'block';
        
        // Opdater notifikationsstil baseret på type
        const notificationElement = document.getElementById('notification');
        notificationElement.className = notificationElement.className.replace(/bg-\w+-\d+ border-l-4 border-\w+-\d+ text-\w+-\d+/, '');
        
        if (type === 'success') {
            notificationElement.classList.add('bg-green-100', 'border-l-4', 'border-green-500', 'text-green-700');
        } else if (type === 'error') {
            notificationElement.classList.add('bg-red-100', 'border-l-4', 'border-red-500', 'text-red-700');
        } else {
            notificationElement.classList.add('bg-blue-100', 'border-l-4', 'border-blue-500', 'text-blue-700');
        }
        
        // Opdater meddelelsen
        document.getElementById('notification-message').textContent = message;
        
        // Vis notifikationen
        notificationElement.style.display = 'block';
        
        // Skjul efter 5 sekunder
        setTimeout(hideNotification, 5000);
    }
    
    function hideNotification() {
        document.getElementById('notification').style.display = 'none';
    }
    
    // Event listeners for Livewire events
    document.addEventListener('DOMContentLoaded', function() {
        // Håndter session flashed notifikation hvis den findes
        @if (session()->has('notification'))
            const notification = @json(session('notification'));
            if (notification && notification.message) {
                showNotification(notification.message, notification.type || 'info');
            }
        @endif
        
        // Livewire 3 method
        document.addEventListener('livewire:initialized', () => {
            try {
                const component = Livewire.first();
                
                component.on('notify', (data) => {
                    console.log('Notify event received', data);
                    showNotification(data[0].message, data[0].type || 'info');
                });
                
                component.on('refresh', () => {
                    console.log('Budget data refreshed');
                });
            } catch (e) {
                console.error('Error setting up Livewire 3 event listeners:', e);
            }
        });
        
        // Backup for Livewire 2 or direct browser events
        window.addEventListener('notify', event => {
            try {
                console.log('Notify browser event received', event);
                const data = event.detail || {};
                if (data[0]) {
                    showNotification(data[0].message, data[0].type || 'info');
                } else if (data.message) {
                    showNotification(data.message, data.type || 'info');
                }
            } catch (e) {
                console.error('Error handling notify event:', e);
            }
        });
        
        window.addEventListener('refresh', event => {
            console.log('Refresh browser event received');
        });

        // Direkte binding til Livewire.on
        if (typeof Livewire !== 'undefined') {
            Livewire.on('notify', data => {
                console.log('Livewire.on notify received', data);
                if (Array.isArray(data) && data.length > 0) {
                    showNotification(data[0].message, data[0].type || 'info');
                } else if (data && data.message) {
                    showNotification(data.message, data.type || 'info');
                }
            });
            
            Livewire.on('refresh', () => {
                console.log('Livewire refresh event received');
                // Opdater data eller UI her
            });
        }

        // Håndter omsætning input
        document.querySelectorAll('input[name^="omsaetning_"]').forEach(input => {
            input.addEventListener('change', function() {
                const nameParts = this.name.split('_');
                const year = nameParts[1];
                const month = nameParts[2];
                const formId = `save-form-${year}-${month}`;
                const hiddenInput = document.querySelector(`form#${formId} input[name="omsaetning_salg_total"]`);
                if (hiddenInput) {
                    hiddenInput.value = this.value;
                }
            });
        });

        // Håndter udgift input
        document.querySelectorAll('input[name^="udgift_"]').forEach(input => {
            input.addEventListener('change', function() {
                const nameParts = this.name.split('_');
                const year = nameParts[1];
                const month = nameParts[2];
                const formId = `save-form-${year}-${month}`;
                const hiddenInput = document.querySelector(`form#${formId} input[name="udgift_variable_kapacitet"]`);
                if (hiddenInput) {
                    hiddenInput.value = this.value;
                }
            });
        });
    });
</script>
