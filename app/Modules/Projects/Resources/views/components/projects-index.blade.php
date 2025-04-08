<!-- filepath: /Projects/Projects/Resources/views/components/projects-index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Projects Dashboard</h1>

    <div class="mb-3">
        <a href="{{ route('projects.refresh') }}" class="btn btn-primary">Refresh Data</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Project Metrics</h2>
        </div>
        <div class="card-body">
            <ul>
                @foreach ($projects as $project)
                    <li>
                        <a href="#">{{ $project['name'] }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection