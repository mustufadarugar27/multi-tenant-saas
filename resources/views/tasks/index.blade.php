@extends('layouts.app')

@section('title', 'Tasks')

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Tasks</h1>
        <p class="mt-0.5 text-sm text-gray-500">{{ $tasks->total() }} total</p>
    </div>
    @can('create', \App\Models\Task::class)
        <a href="{{ route('tenant.tasks.create') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold
                  text-white shadow-sm hover:bg-indigo-500 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            New Task
        </a>
    @endcan
</div>
@endsection

@section('content')

{{-- Table --}}
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    @if ($tasks->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <p class="text-gray-500 font-medium">No tasks found</p>
            <p class="text-gray-400 text-sm mt-1">Create your first task to get started.</p>
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 pl-6 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Title</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Priority</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Assignee</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Due date</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Project</th>
                    <th class="relative py-3 pl-3 pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach ($tasks as $task)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3.5 pl-6 pr-3">
                            <a href="{{ route('tenant.tasks.show', $task) }}"
                               class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                                {{ $task->title }}
                            </a>
                            @if ($task->isOverdue())
                                <span class="ml-2 inline-flex items-center rounded-full bg-red-50 px-1.5 py-0.5 text-xs font-medium text-red-600 ring-1 ring-inset ring-red-200">
                                    Overdue
                                </span>
                            @elseif ($task->isDueSoon())
                                <span class="ml-2 inline-flex items-center rounded-full bg-amber-50 px-1.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-200">
                                    Due soon
                                </span>
                            @endif
                            @if ($task->description)
                                <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ $task->description }}</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-3.5">
                            @include('tasks._status_badge', ['status' => $task->status])
                        </td>
                        <td class="whitespace-nowrap px-3 py-3.5">
                            @include('tasks._priority_badge', ['priority' => $task->priority])
                        </td>
                        <td class="whitespace-nowrap px-3 py-3.5 text-sm text-gray-600">
                            {{ $task->assignee?->name ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-3.5 text-sm text-gray-600">
                            {{ $task->due_date?->format('M j, Y') ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-3.5 text-sm text-gray-600">
                            @if ($task->project)
                                <a href="{{ route('tenant.projects.show', $task->project) }}"
                                   class="text-indigo-600 hover:underline">
                                    {{ $task->project->name }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="whitespace-nowrap py-3.5 pl-3 pr-6 text-right text-sm">
                            <div class="flex items-center justify-end gap-3">
                                @can('update', $task)
                                    <a href="{{ route('tenant.tasks.edit', $task) }}"
                                       class="text-gray-500 hover:text-indigo-600 transition font-medium">Edit</a>
                                @endcan
                                @can('delete', $task)
                                    <form method="POST"
                                          action="{{ route('tenant.tasks.destroy', $task) }}"
                                          onsubmit="return confirm('Delete this task?')">
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
        @if ($tasks->hasPages())
            <div class="border-t border-gray-200 px-6 py-3">
                {{ $tasks->withQueryString()->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
