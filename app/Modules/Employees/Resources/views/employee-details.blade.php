@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ $employee->name ?? 'New Employee' }}
            </h1>
            <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Back to List
            </a>
        </div>
        
        @livewire('employee-details', ['employee' => $employee])
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        console.log('Livewire component loaded for employee details');
        
        // Listen for edit state changes
        Livewire.on('editStateChanged', data => {
            console.log('Edit state changed:', data);
        });
        
        // Listen for employee updates
        Livewire.on('employeeUpdated', () => {
            console.log('Employee updated event received');
        });
        
        // Monitor Livewire communication
        Livewire.hook('message.sent', (message, component) => {
            console.log('Livewire message sent:', message);
        });
        
        Livewire.hook('message.failed', (message, component) => {
            console.error('Livewire message failed:', message);
            console.error('Component:', component);
        });
        
        Livewire.hook('message.received', (message, component) => {
            console.log('Livewire message received:', message);
        });
        
        // Log component initialization
        Livewire.hook('component.initialized', component => {
            console.log('Component initialized:', component.id);
        });
    });
</script>
@endpush
@endsection 