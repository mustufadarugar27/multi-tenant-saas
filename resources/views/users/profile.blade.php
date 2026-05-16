@extends('layouts.app')

@section('title', 'My Profile')

@section('header')
<h1 class="text-xl font-semibold text-gray-900">My Profile</h1>
@endsection

@section('content')
<div class="max-w-lg space-y-6">

    {{-- Personal info --}}
    <form method="POST" action="{{ route('tenant.profile.update') }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Personal information</h2>
            </div>

            <div class="px-6 py-5 space-y-5">

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Full name <span class="text-red-500">*</span>
                    </label>
                    <input id="name" name="name" type="text"
                           value="{{ old('name', $user->name) }}"
                           maxlength="255" autocomplete="name"
                           class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email (read-only) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email address</label>
                    <input type="email" value="{{ $user->email }}" disabled
                           class="mt-1 block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2
                                  text-sm text-gray-500 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-400">Email cannot be changed.</p>
                </div>

                {{-- Role (read-only) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role</label>
                    <input type="text" value="{{ \App\Support\LangTranslations::attr('user_role', $user->role, 'label', ucfirst(str_replace('_', ' ', $user->role))) }}" disabled
                           class="mt-1 block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2
                                  text-sm text-gray-500 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-400">Role is managed by your administrator.</p>
                </div>

            </div>
        </div>

        {{-- Change password --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Change password</h2>
                <p class="mt-0.5 text-xs text-gray-500">Leave blank to keep your current password.</p>
            </div>

            <div class="px-6 py-5 space-y-5">

                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700">
                        Current password
                    </label>
                    <input id="current_password" name="current_password" type="password"
                           autocomplete="current-password"
                           class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  {{ $errors->has('current_password') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @error('current_password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        New password
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

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        Confirm new password
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                           autocomplete="new-password"
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

            </div>
        </div>

        <div class="flex items-center justify-end">
            <button type="submit"
                    class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white
                           shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2
                           focus:ring-indigo-600 focus:ring-offset-2 transition">
                Save changes
            </button>
        </div>
    </form>
</div>
@endsection
