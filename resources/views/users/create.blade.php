@extends('layouts.app')

@section('title', 'Create User')

@section('header')
<div class="flex items-center gap-3">
    <a href="{{ url()->previous() }}"
       class="text-gray-400 hover:text-gray-600 transition">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
    </a>
    <h1 class="text-xl font-semibold text-gray-900">Create User</h1>
</div>
@endsection

@section('content')
<div class="max-w-lg">
    <form method="POST" action="{{ route('tenant.users.store') }}" class="space-y-6">
        @csrf

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">User details</h2>
            </div>

            <div class="px-6 py-5 space-y-5">

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Full name <span class="text-red-500">*</span>
                    </label>
                    <input id="name" name="name" type="text"
                           value="{{ old('name') }}"
                           maxlength="255" autocomplete="name"
                           class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email address <span class="text-red-500">*</span>
                    </label>
                    <input id="email" name="email" type="email"
                           value="{{ old('email') }}"
                           maxlength="255" autocomplete="email"
                           class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input id="password" name="password" type="password"
                           autocomplete="new-password"
                           class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        Confirm password <span class="text-red-500">*</span>
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                           autocomplete="new-password"
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Role --}}
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select id="role" name="role"
                            class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500
                                   {{ $errors->has('role') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                        <option value="">Select a role…</option>
                        @foreach ($assignableRoles as $role)
                            <option value="{{ $role->value }}"
                                    {{ old('role') === $role->value ? 'selected' : '' }}>
                                {{ $role->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

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
                Create user
            </button>
        </div>
    </form>
</div>
@endsection
