@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h1 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
            {{ ucfirst(tenant('name')) }}
        </h1>
        <p class="mt-2 text-center text-sm text-gray-500">Sign in to your account</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white px-6 py-8 shadow-sm ring-1 ring-gray-200 rounded-xl sm:px-8">
            <form method="POST" action="{{ route('tenant.login.post') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required
                               value="{{ old('email') }}"
                               class="block w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                                      transition focus:outline-none focus:ring-2 focus:ring-indigo-500
                                      {{ $errors->has('email') ? 'border-red-400 bg-red-50 text-red-900' : 'border-gray-300 text-gray-900' }}">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm
                                      text-gray-900 transition focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Remember me
                    </label>
                </div>

                <button type="submit"
                        class="w-full flex justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold
                               text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2
                               focus:ring-indigo-600 focus:ring-offset-2 transition">
                    Sign in
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
