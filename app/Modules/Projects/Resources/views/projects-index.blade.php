@extends('layouts.app')

@push('scripts')
    @livewireScripts
@endpush

@push('styles')
    @livewireStyles
@endpush

@section('content')
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Projects Overview</h1>
            <div class="flex items-center space-x-4">
                <!-- Month Selector -->
                <select onchange="window.location.href='{{ route('projects.index') }}?period=' + this.value"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    @foreach($periods as $period)
                        <option value="{{ $period['value'] }}" {{ $selectedPeriod === $period['value'] ? 'selected' : '' }}>
                            {{ $period['label'] }}
                        </option>
                    @endforeach
                </select>
                <!-- Refresh Button -->
                <form action="{{ route('projects.refresh') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="period" value="{{ $selectedPeriod }}">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh Data
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Projects</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ count($projects) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Hours</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalMonthlyHours, 1) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Invoice Ready</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalInvoiceReadyHours, 1) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            @livewire('projects-table', ['projects' => $projects], key('projects-table'))
        </div>
    </div>
@endsection
