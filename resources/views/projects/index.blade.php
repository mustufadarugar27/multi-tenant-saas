@extends('layouts.app')

@section('title', 'Projects')

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Projects</h1>
        <p class="mt-0.5 text-sm text-gray-500">{{ $projects->total() }} total</p>
    </div>
    @can('create', \App\Domain\Project\Models\Project::class)
        <a href="{{ route('tenant.projects.create') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold
                  text-white shadow-sm hover:bg-indigo-500 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            New Project
        </a>
    @endcan
</div>
@endsection

@section('content')

{{-- Filters --}}
<form method="GET" action="{{ route('tenant.projects.index') }}" class="mb-5">
    <div class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Search projects…"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}"
                            {{ ($filters['status'] ?? '') === $status->value ? 'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                   class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                   class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Sort by</label>
            <select name="sort_by"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach (['created_at' => 'Created', 'name' => 'Name', 'status' => 'Status', 'start_date' => 'Start Date', 'budget' => 'Budget'] as $val => $label)
                    <option value="{{ $val }}" {{ ($filters['sort_by'] ?? 'created_at') === $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="sort_dir"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="desc" {{ ($filters['sort_dir'] ?? 'desc') === 'desc' ? 'selected' : '' }}>Newest first</option>
                <option value="asc"  {{ ($filters['sort_dir'] ?? '') === 'asc'  ? 'selected' : '' }}>Oldest first</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white
                           hover:bg-indigo-500 transition shadow-sm">
                Filter
            </button>
            <a href="{{ route('tenant.projects.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium
                      text-gray-700 hover:bg-gray-50 transition shadow-sm">
                Clear
            </a>
        </div>
    </div>
</form>

{{-- Table --}}
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    @if ($projects->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/>
            </svg>
            <p class="text-gray-500 font-medium">No projects found</p>
            <p class="text-gray-400 text-sm mt-1">Get started by creating a new project.</p>
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 pl-6 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Budget</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Start Date</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">End Date</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Created by</th>
                    <th class="relative py-3 pl-3 pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach ($projects as $project)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3.5 pl-6 pr-3">
                            <a href="{{ route('tenant.projects.show', $project) }}"
                               class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                                {{ $project->name }}
                            </a>
                            @if ($project->description)
                                <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ $project->description }}</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-3.5">
                            @include('projects._status_badge', ['status' => $project->status])
                        </td>
                        <td class="whitespace-nowrap px-3 py-3.5 text-sm text-gray-600">
                            {{ $project->budget !== null ? '$' . number_format((float) $project->budget, 2) : '—' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-3.5 text-sm text-gray-600">
                            {{ $project->start_date?->format('M j, Y') ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-3.5 text-sm text-gray-600">
                            {{ $project->end_date?->format('M j, Y') ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-3.5 text-sm text-gray-600">
                            {{ $project->creator?->name ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap py-3.5 pl-3 pr-6 text-right text-sm">
                            <div class="flex items-center justify-end gap-3">
                                @can('update', $project)
                                    <a href="{{ route('tenant.projects.edit', $project) }}"
                                       class="text-gray-500 hover:text-indigo-600 transition font-medium">Edit</a>
                                @endcan
                                @can('delete', $project)
                                    <form method="POST"
                                          action="{{ route('tenant.projects.destroy', $project) }}"
                                          onsubmit="return confirm('Delete this project?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 transition font-medium">
                                            Delete
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        @if ($projects->hasPages())
            <div class="border-t border-gray-200 px-6 py-3">
                {{ $projects->withQueryString()->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
