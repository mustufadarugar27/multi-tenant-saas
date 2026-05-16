{{--
    Shared form partial for task create and edit.
    Variables expected:
      $task       — model instance (new or existing)
      $statuses   — array of TaskStatus cases
      $priorities — array of TaskPriority cases
      $projects   — collection of Project models
      $users      — collection of User models
      $action     — form action URL
      $method     — PUT | POST
--}}
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Task details</h2>
        </div>

        <div class="px-6 py-5 space-y-5">

            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">
                    Title <span class="text-red-500">*</span>
                </label>
                <input id="title" name="title" type="text"
                       value="{{ old('title', $task->title ?? '') }}"
                       maxlength="255" autofocus
                       placeholder="Enter task title…"
                       class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-500
                              {{ $errors->has('title') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                @error('title')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="4" maxlength="10000"
                          placeholder="Add more details about this task…"
                          class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                 focus:outline-none focus:ring-2 focus:ring-indigo-500
                                 {{ $errors->has('description') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">{{ old('description', $task->description ?? '') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Project --}}
            <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700">
                    Project <span class="text-red-500">*</span>
                </label>
                <select id="project_id" name="project_id"
                        class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500
                               {{ $errors->has('project_id') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    <option value="">— Select project —</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}"
                                {{ old('project_id', $task->project_id ?? '') === $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
                @error('project_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status + Priority --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status"
                            class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500
                                   {{ $errors->has('status') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}"
                                    {{ old('status', $task->status ?? 'todo') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                    <select id="priority" name="priority"
                            class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500
                                   {{ $errors->has('priority') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->value }}"
                                    {{ old('priority', $task->priority ?? 'medium') === $priority->value ? 'selected' : '' }}>
                                {{ $priority->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('priority')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Assignee --}}
            <div>
                <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assignee</label>
                <select id="assigned_to" name="assigned_to"
                        class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500
                               {{ $errors->has('assigned_to') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    <option value="">— Unassigned —</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}"
                                {{ old('assigned_to', $task->assigned_to ?? '') === $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('assigned_to')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Due date + Estimated hours --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700">Due date</label>
                    <input id="due_date" name="due_date" type="date"
                           value="{{ old('due_date', $task->due_date?->format('Y-m-d') ?? '') }}"
                           class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  {{ $errors->has('due_date') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('due_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="estimated_hours" class="block text-sm font-medium text-gray-700">Estimated hours</label>
                    <input id="estimated_hours" name="estimated_hours" type="number" step="0.5" min="0" max="9999.99"
                           value="{{ old('estimated_hours', $task->estimated_hours ?? '') }}"
                           placeholder="e.g. 4"
                           class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  {{ $errors->has('estimated_hours') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('estimated_hours')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ url()->previous() }}"
           class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium
                  text-gray-700 shadow-sm hover:bg-gray-50 transition">
            Cancel
        </a>
        <button type="submit"
                class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white
                       shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2
                       focus:ring-indigo-600 focus:ring-offset-2 transition">
            {{ $method === 'PUT' ? 'Save changes' : 'Create task' }}
        </button>
    </div>
</form>
