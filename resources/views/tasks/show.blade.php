@extends('layouts.app')

@section('title', $task->title)

@section('header')
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('tenant.tasks.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-semibold text-gray-900">{{ $task->title }}</h1>
                    @if ($task->isOverdue())
                        <span
                            class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-600 ring-1 ring-inset ring-red-200">
                            Overdue
                        </span>
                    @elseif ($task->isDueSoon())
                        <span
                            class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-200">
                            Due soon
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-0.5">Created {{ $task->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $task)
                <a href="{{ route('tenant.tasks.edit', $task) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2
                      text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                    Edit
                </a>
            @endcan
            @can('delete', $task)
                <form method="POST" action="{{ route('tenant.tasks.destroy', $task) }}"
                    onsubmit="return confirm('Delete this task? It can be restored later.')">
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

        {{-- Left column: main content --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Overview card --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Overview</h2>
                </div>
                <dl class="divide-y divide-gray-100">
                    <div class="px-6 py-3.5 flex gap-4">
                        <dt class="w-36 shrink-0 text-sm font-medium text-gray-500">Status</dt>
                        <dd>@include('tasks._status_badge', ['status' => $task->status])</dd>
                    </div>
                    <div class="px-6 py-3.5 flex gap-4">
                        <dt class="w-36 shrink-0 text-sm font-medium text-gray-500">Priority</dt>
                        <dd>@include('tasks._priority_badge', ['priority' => $task->priority])</dd>
                    </div>
                    <div class="px-6 py-3.5 flex gap-4">
                        <dt class="w-36 shrink-0 text-sm font-medium text-gray-500">Description</dt>
                        <dd class="text-sm text-gray-900 leading-relaxed whitespace-pre-wrap">
                            {{ $task->description ?? '—' }}
                        </dd>
                    </div>
                    @if ($task->completed_at)
                        <div class="px-6 py-3.5 flex gap-4">
                            <dt class="w-36 shrink-0 text-sm font-medium text-gray-500">Completed at</dt>
                            <dd class="text-sm text-gray-900">{{ $task->completed_at->setTimezone('Asia/Kolkata')->format('M j, Y g:i A') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Task metadata --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Details</h2>
                </div>
                <dl class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                    <div class="px-6 py-3.5">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Project</dt>
                        <dd class="text-sm text-gray-900">
                            @if ($task->project)
                                <a href="{{ route('tenant.projects.show', $task->project) }}"
                                    class="text-indigo-600 hover:underline">
                                    {{ $task->project->name }}
                                </a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </dd>
                    </div>
                    <div class="px-6 py-3.5">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Assignee</dt>
                        <dd class="text-sm text-gray-900">
                            @if ($task->assignee)
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-xs font-semibold text-indigo-700">
                                            {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    {{ $task->assignee->name }}
                                </div>
                            @else
                                <span class="text-gray-400">Unassigned</span>
                            @endif
                        </dd>
                    </div>
                    <div class="px-6 py-3.5">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Created by</dt>
                        <dd class="text-sm text-gray-900">{{ $task->creator?->name ?? 'Unknown' }}</dd>
                    </div>
                    <div class="px-6 py-3.5">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Due date</dt>
                        <dd class="text-sm {{ $task->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                            {{ $task->due_date?->format('F j, Y') ?? '—' }}
                        </dd>
                    </div>
                    <div class="px-6 py-3.5">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Estimated hours</dt>
                        <dd class="text-sm text-gray-900">
                            {{ $task->estimated_hours !== null ? number_format((float) $task->estimated_hours, 1) . ' h' : '—' }}
                        </dd>
                    </div>
                    @if ($task->actual_hours !== null)
                        <div class="px-6 py-3.5">
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Actual hours</dt>
                            <dd class="text-sm text-gray-900">{{ number_format((float) $task->actual_hours, 1) }} h</dd>
                        </div>
                    @endif
                    <div class="px-6 py-3.5">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Created</dt>
                        <dd class="text-sm text-gray-900">{{ $task->created_at->setTimezone('Asia/Kolkata')->format('M j, Y g:i A') }}</dd>
                    </div>
                    <div class="px-6 py-3.5">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Last updated</dt>
                        <dd class="text-sm text-gray-900">{{ $task->updated_at->setTimezone('Asia/Kolkata')->format('M j, Y g:i A') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Attachments --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">
                        Attachments
                        <span
                            class="ml-1.5 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                            {{ $attachments->count() }}
                        </span>
                    </h2>
                </div>

                @if ($attachments->isNotEmpty())
                    <ul class="divide-y divide-gray-100">
                        @foreach ($attachments as $attachment)
                            <li class="flex items-center gap-4 px-6 py-3.5">
                                <div class="h-9 w-9 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                    <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $attachment->filename }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $attachment->uploader?->name ?? 'Unknown' }} &middot;
                                        {{ $attachment->created_at->format('M j, Y') }}
                                        @if ($attachment->size)
                                            &middot; {{ round($attachment->size / 1024, 1) }} KB
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <a href="{{ route('tenant.tasks.attachments.download', [$task, $attachment]) }}"
                                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
                                        Download
                                    </a>
                                    @can('deleteAttachment', $task)
                                        <form method="POST"
                                            action="{{ route('tenant.tasks.attachments.destroy', [$task, $attachment]) }}"
                                            onsubmit="return confirm('Remove this attachment?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 transition">
                                                Remove
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="px-6 py-6 text-center">
                        <p class="text-sm text-gray-400">No attachments yet.</p>
                    </div>
                @endif

                {{-- Upload form --}}
                @can('uploadAttachment', $task)
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                        <form method="POST" action="{{ route('tenant.tasks.attachments.store', $task) }}"
                            enctype="multipart/form-data">
                            @csrf
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Upload file</label>
                            <div class="flex items-center gap-3">
                                <input type="file" name="file"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.gif,.zip"
                                    class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0
                                          file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-medium
                                          file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                <button type="submit"
                                    class="shrink-0 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white
                                           hover:bg-indigo-500 transition shadow-sm">
                                    Upload
                                </button>
                            </div>
                            @error('file')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-400">Max 20 MB. Accepted: PDF, Word, Excel, images, ZIP.</p>
                        </form>
                    </div>
                @endcan
            </div>

            </div>

    {{-- Activity / History Modal --}}
    <div id="task-history-modal"
        style="display:none; position:fixed; inset:0; z-index:50; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); padding:1rem; backdrop-filter:blur(4px);">
        <div style="width:100%; max-width:36rem; max-height:80vh; display:flex; flex-direction:column; border-radius:1rem; background:#fff; box-shadow:0 25px 50px -12px rgba(0,0,0,0.4); overflow:hidden;">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 shrink-0 bg-indigo-600">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-sm font-semibold text-white">Activity History</h2>
                </div>
                <button id="closeModalBtn" class="text-indigo-200 hover:text-white transition text-2xl leading-none font-light">
                    &times;
                </button>
            </div>

            <!-- Body -->
            <div class="overflow-y-auto flex-1">
                @if ($history->isNotEmpty())
                    <ul class="divide-y divide-gray-100">
                        @foreach ($history as $entry)
                            <li class="px-6 py-3.5 flex items-start gap-3">
                                <div class="mt-0.5 h-7 w-7 rounded-full bg-indigo-50 flex items-center justify-center shrink-0">
                                    <svg class="h-3.5 w-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700">
                                        <span class="font-semibold text-gray-900">{{ $entry->actor?->name ?? 'System' }}</span>
                                        @switch($entry->event)
                                            @case('status_changed') changed status @break
                                            @case('field_changed') changed {{ $entry->field }} @break
                                            @case('comment_added') added a comment @break
                                            @case('attachment_uploaded') uploaded an attachment @break
                                            @default {{ $entry->event }}
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
                                <span class="text-xs text-gray-400 whitespace-nowrap shrink-0">{{ $entry->created_at->diffForHumans() }}</span>
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
                                {{-- Previous --}}
                                @if ($history->onFirstPage())
                                    <span class="inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-300 cursor-not-allowed">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </span>
                                @else
                                    <a href="{{ $history->previousPageUrl() }}"
                                        class="inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </a>
                                @endif

                                {{-- Page numbers --}}
                                @foreach ($history->getUrlRange(1, $history->lastPage()) as $page => $url)
                                    @if ($page == $history->currentPage())
                                        <span class="inline-flex items-center justify-center h-7 w-7 rounded-md bg-indigo-600 text-white text-xs font-semibold">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $url }}"
                                            class="inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-600 text-xs hover:bg-indigo-50 hover:text-indigo-600 transition">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach

                                {{-- Next --}}
                                @if ($history->hasMorePages())
                                    <a href="{{ $history->nextPageUrl() }}"
                                        class="inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                @else
                                    <span class="inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-300 cursor-not-allowed">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    <div class="px-6 py-10 text-center">
                        <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
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

        {{-- Right sidebar --}}
        <div class="space-y-5">

            {{-- Quick stats --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Summary</h2>
                </div>
                <div class="grid grid-cols-2 divide-x divide-gray-100">
                    <div class="px-6 py-4 text-center">
                        <p class="text-2xl font-bold text-gray-900">{{ $task->comments_count }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Comments</p>
                    </div>
                    <div class="px-6 py-4 text-center">
                        <p class="text-2xl font-bold text-gray-900">{{ $task->attachments_count }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Files</p>
                    </div>
                </div>
            </div>

            {{-- Comments --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">
                        Comments
                        <span
                            class="ml-1.5 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                            {{ $comments->count() }}
                        </span>
                    </h2>
                </div>

                {{-- Comment list --}}
                @if ($comments->isNotEmpty())
                    <ul class="divide-y divide-gray-100">
                        @foreach ($comments as $comment)
                            <li class="px-6 py-4" id="comment-{{ $comment->id }}">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                                        <span class="text-xs font-semibold text-indigo-700">
                                            {{ strtoupper(substr($comment->author->name ?? '?', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <span
                                                class="text-sm font-medium text-gray-900">{{ $comment->author->name ?? 'Unknown' }}</span>
                                            <span
                                                class="text-xs text-gray-400 whitespace-nowrap">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>

                                        {{-- View mode --}}
                                        <div
                                            class="mt-1 text-sm text-gray-700 leading-relaxed whitespace-pre-wrap comment-body">
                                            {{ $comment->content }}</div>

                                        {{-- Edit form (hidden by default) --}}
                                        @can('update', $comment)
                                            <form method="POST"
                                                action="{{ route('tenant.tasks.comments.update', [$task, $comment]) }}"
                                                class="mt-2 hidden comment-edit-form">
                                                @csrf @method('PATCH')
                                                <textarea name="content" rows="3" maxlength="5000"
                                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm
                                                             focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ $comment->content }}</textarea>
                                                <div class="mt-2 flex gap-2">
                                                    <button type="submit"
                                                        class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500 transition">
                                                        Save
                                                    </button>
                                                    <button type="button" onclick="toggleEdit('{{ $comment->id }}')"
                                                        class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </form>
                                            <div class="mt-1 flex items-center gap-3 comment-actions">
                                                <button type="button" onclick="toggleEdit('{{ $comment->id }}')"
                                                    class="text-xs text-gray-400 hover:text-indigo-600 transition">
                                                    Edit
                                                </button>
                                                <form method="POST"
                                                    action="{{ route('tenant.tasks.comments.destroy', [$task, $comment]) }}"
                                                    onsubmit="return confirm('Delete this comment?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="text-xs text-gray-400 hover:text-red-600 transition">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        @endcan
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm text-gray-400">No comments yet. Be the first to comment.</p>
                    </div>
                @endif

                {{-- Add comment form --}}
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    <form method="POST" action="{{ route('tenant.tasks.comments.store', $task) }}">
                        @csrf
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1.5">Add a comment</label>
                        <textarea id="content" name="content" rows="3" maxlength="5000" placeholder="Write a comment…"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm
                                     focus:outline-none focus:ring-2 focus:ring-indigo-500
                                     {{ $errors->has('content') ? 'border-red-400 bg-red-50' : '' }}">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-2 flex justify-end">
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white
                                       hover:bg-indigo-500 transition shadow-sm">
                                Post comment
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Restore button for soft-deleted --}}
            @if ($task->trashed())
                @can('restore', $task)
                    <form method="POST" action="{{ route('tenant.tasks.restore', $task->id) }}">
                        @csrf
                        <button type="submit"
                            class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white
                                   shadow-sm hover:bg-green-500 transition">
                            Restore task
                        </button>
                    </form>
                @endcan
            @endif

        </div>
    </div>

    <script>
        function toggleEdit(commentId) {
            const li = document.getElementById('comment-' + commentId);
            li.querySelector('.comment-body').classList.toggle('hidden');
            li.querySelector('.comment-edit-form').classList.toggle('hidden');
            li.querySelector('.comment-actions').classList.toggle('hidden');
        }
    </script>
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
