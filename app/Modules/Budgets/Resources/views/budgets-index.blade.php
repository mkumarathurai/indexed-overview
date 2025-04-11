@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Budgets Overview</h1>
        </div>
        
        @livewire('budgets-index')
    </div>
@endsection
