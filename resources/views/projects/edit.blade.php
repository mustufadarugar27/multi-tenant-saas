@extends('layouts.app')

@section('title', 'Edit — ' . $project->name)

@section('header')
<div class="flex items-center gap-3">
    <a href="{{ route('tenant.projects.show', $project) }}"
       class="text-gray-400 hover:text-gray-600 transition">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
    </a>
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Edit Project</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $project->name }}</p>
    </div>
</div>
@endsection

@section('content')
<div class="w-screen px-4 py-6">
    @include('projects._form', [
        'project'  => $project,
        'statuses' => $statuses,
        'action'   => route('tenant.projects.update', $project),
        'method'   => 'PUT',
    ])
</div>
@endsection
