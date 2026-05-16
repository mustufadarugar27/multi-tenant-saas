{{--
    Shared form partial for create and edit.
    Variables expected:
      $project  — model instance (new or existing)
      $statuses — array of ProjectStatus cases
      $action   — form action URL
      $method   — PUT | POST
--}}
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Project details</h2>
        </div>

        <div class="px-6 py-5 space-y-5">

            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Name <span class="text-red-500">*</span>
                </label>
                <input id="name" name="name" type="text"
                       value="{{ old('name', $project->name ?? '') }}"
                       maxlength="255"
                       class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-500
                              {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="4" maxlength="5000"
                          class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                 focus:outline-none focus:ring-2 focus:ring-indigo-500
                                 {{ $errors->has('description') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">{{ old('description', $project->description ?? '') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status"
                        class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500
                               {{ $errors->has('status') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}"
                                {{ old('status', $project->status ?? 'draft') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Dates --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start date</label>
                    <input id="start_date" name="start_date" type="date"
                           value="{{ old('start_date', $project->start_date?->format('Y-m-d') ?? '') }}"
                           class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  {{ $errors->has('start_date') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('start_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End date</label>
                    <input id="end_date" name="end_date" type="date"
                           value="{{ old('end_date', $project->end_date?->format('Y-m-d') ?? '') }}"
                           class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  {{ $errors->has('end_date') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('end_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Budget --}}
            <div>
                <label for="budget" class="block text-sm font-medium text-gray-700">Budget</label>
                <div class="mt-1 relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">$</span>
                    <input id="budget" name="budget" type="number" step="0.01" min="0" max="999999999.99"
                           value="{{ old('budget', $project->budget ?? '') }}"
                           class="block w-full rounded-lg border pl-7 pr-3 py-2 text-sm shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  {{ $errors->has('budget') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                </div>
                @error('budget')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
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
            {{ $method === 'PUT' ? 'Save changes' : 'Create project' }}
        </button>
    </div>
</form>
