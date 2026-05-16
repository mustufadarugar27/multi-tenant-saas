@extends('layouts.app')

@section('title', $project->name)

@section('header')
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('tenant.projects.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $project->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">Created {{ $project->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $project)
                <a href="{{ route('tenant.projects.edit', $project) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2
                      text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                    Edit
                </a>
            @endcan
            @can('delete', $project)
                <form method="POST" action="{{ route('tenant.projects.destroy', $project) }}"
                    onsubmit="return confirm('Delete this project? It can be restored later.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm
                               font-medium text-white shadow-sm hover:bg-red-500 transition">
                        Delete
                    </button>
                </form>
            @endcan
            <button id="open-history"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2
                      text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Overview card --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Overview</h2>
                </div>
                <dl class="divide-y divide-gray-100">
                    <div class="px-6 py-3.5 flex gap-4">
                        <dt class="w-32 shrink-0 text-sm font-medium text-gray-500">Status</dt>
                        <dd>@include('projects._status_badge', ['status' => $project->status])</dd>
                    </div>
                    <div class="px-6 py-3.5 flex gap-4">
                        <dt class="w-32 shrink-0 text-sm font-medium text-gray-500">Description</dt>
                        <dd class="text-sm text-gray-900 leading-relaxed">
                            {{ $project->description ?? '—' }}
                        </dd>
                    </div>
                    <div class="px-6 py-3.5 flex gap-4">
                        <dt class="w-32 shrink-0 text-sm font-medium text-gray-500">Budget</dt>
                        <dd class="text-sm text-gray-900">
                            {{ $project->budget !== null ? '$' . number_format((float) $project->budget, 2) : '—' }}
                        </dd>
                    </div>
                    <div class="px-6 py-3.5 flex gap-4">
                        <dt class="w-32 shrink-0 text-sm font-medium text-gray-500">Start date</dt>
                        <dd class="text-sm text-gray-900">
                            {{ $project->start_date?->format('F j, Y') ?? '—' }}
                        </dd>
                    </div>
                    <div class="px-6 py-3.5 flex gap-4">
                        <dt class="w-32 shrink-0 text-sm font-medium text-gray-500">End date</dt>
                        <dd class="text-sm text-gray-900">
                            {{ $project->end_date?->format('F j, Y') ?? '—' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Sidebar info --}}
        <div class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Details</h2>
                </div>
                <dl class="divide-y divide-gray-100">
                    <div class="px-6 py-3.5">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Created by</dt>
                        <dd class="text-sm text-gray-900">{{ $project->creator?->name ?? 'Unknown' }}</dd>
                    </div>
                    <div class="px-6 py-3.5">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Created</dt>
                        <dd class="text-sm text-gray-900">{{ $project->created_at->setTimezone('Asia/Kolkata')->format('M j, Y g:i A') }}</dd>
                    </div>
                    <div class="px-6 py-3.5">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Last updated</dt>
                        <dd class="text-sm text-gray-900">{{ $project->updated_at->setTimezone('Asia/Kolkata')->format('M j, Y g:i A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
    <div class="mt-2">
        {{-- Activity / History --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Tasks</h2>
            </div>
            @if (isset($projectTasks))
                <ul class="divide-y divide-gray-100">
                    @foreach ($projectTasks as $entry)
                        <li class="px-6 py-3.5 flex items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-700">
                                    <a href="{{ route('tenant.tasks.show', $entry->id) }}"
                                        class="text-indigo-600 hover:underline">
                                        {{ $entry->title }}
                                    </a>
                                </p>
                                <p class="mt-0.5 text-xs text-gray-400">
                                    {{ $entry->status }}
                                </p>
                            </div>
                            <span
                                class="text-xs text-gray-400 whitespace-nowrap shrink-0">{{ $entry->created_at->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="px-6 py-6 text-center">
                    <p class="text-sm text-gray-400">No Task recorded yet.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Activity / History Modal --}}
    <div id="task-history-modal"
        style="display:none; position:fixed; inset:0; z-index:50; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); padding:1rem; backdrop-filter:blur(4px);">
        <div
            style="width:100%; max-width:36rem; max-height:80vh; display:flex; flex-direction:column; border-radius:1rem; background:#fff; box-shadow:0 25px 50px -12px rgba(0,0,0,0.4); overflow:hidden;">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 shrink-0 bg-indigo-600">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-sm font-semibold text-white">Activity History</h2>
                </div>
                <button id="closeModalBtn"
                    class="text-indigo-200 hover:text-white transition text-2xl leading-none font-light">
                    &times;
                </button>
            </div>

            <!-- Body -->
            <div class="overflow-y-auto flex-1">
                @if ($history->isNotEmpty())
                    <ul class="divide-y divide-gray-100">
                        @foreach ($history as $entry)
                            <li class="px-6 py-3.5 flex items-start gap-3">
                                <div
                                    class="mt-0.5 h-7 w-7 rounded-full bg-indigo-50 flex items-center justify-center shrink-0">
                                    <svg class="h-3.5 w-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700">
                                        <span
                                            class="font-semibold text-gray-900">{{ $entry->actor?->name ?? 'System' }}</span>
                                        @switch($entry->event)
                                            @case('status_changed')
                                                changed status
                                            @break

                                            @case('field_changed')
                                                changed {{ $entry->field }}
                                            @break

                                            @case('project_created')
                                                created this project
                                            @break

                                            @case('project_restored')
                                                restored this project
                                            @break

                                            @default
                                                {{ $entry->event }}
                                        @endswitch
                                    </p>
                                    @if ($entry->old_value || $entry->new_value)
                                        <p class="mt-0.5 text-xs text-gray-400">
                                            @if ($entry->old_value)
                                                <span class="line-through">{{ $entry->old_value }}</span>
                                                &rarr;
                                            @endif
                                            {{ $entry->new_value }}
                                        </p>
                                    @endif
                                </div>
                                <span
                                    class="text-xs text-gray-400 whitespace-nowrap shrink-0">{{ $entry->created_at->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Pagination --}}
                    @if ($history->hasPages())
                        <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 bg-gray-50">
                            <span class="text-xs text-gray-500">
                                Showing {{ $history->firstItem() }}–{{ $history->lastItem() }} of {{ $history->total() }}
                            </span>
                            <div class="flex items-center gap-1">
                                @if ($history->onFirstPage())
                                    <span
                                        class="inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-300 cursor-not-allowed">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </span>
                                @else
                                    <a href="{{ $history->previousPageUrl() }}"
                                        class="inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </a>
                                @endif

                                @foreach ($history->getUrlRange(1, $history->lastPage()) as $page => $url)
                                    @if ($page == $history->currentPage())
                                        <span
                                            class="inline-flex items-center justify-center h-7 w-7 rounded-md bg-indigo-600 text-white text-xs font-semibold">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $url }}"
                                            class="inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-600 text-xs hover:bg-indigo-50 hover:text-indigo-600 transition">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach

                                @if ($history->hasMorePages())
                                    <a href="{{ $history->nextPageUrl() }}"
                                        class="inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                @else
                                    <span
                                        class="inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-300 cursor-not-allowed">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    <div class="px-6 py-10 text-center">
                        <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-gray-400">No activity recorded yet.</p>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="flex justify-end px-6 py-4 border-t border-gray-100 shrink-0">
                <button id="closeFooterBtn"
                    class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition shadow-sm">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('task-history-modal');

        function openModal() {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        document.getElementById('open-history').addEventListener('click', openModal);
        document.getElementById('closeModalBtn').addEventListener('click', closeModal);
        document.getElementById('closeFooterBtn').addEventListener('click', closeModal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });

        // Re-open modal automatically when paginating (history_page param in URL)
        if (new URLSearchParams(window.location.search).has('history_page')) {
            openModal();
        }
    </script>
@endsection
