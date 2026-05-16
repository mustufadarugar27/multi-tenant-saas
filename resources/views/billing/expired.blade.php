<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto p-8">
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Subscription Expired</h1>
            <p class="text-gray-500 mb-8">
                Your subscription has expired. Please renew to continue accessing your workspace.
            </p>

            <div class="space-y-3">
                <a href="{{ route('billing.index') }}"
                   class="block w-full bg-indigo-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-indigo-700 transition">
                    Renew Subscription
                </a>
                <form method="POST" action="{{ route('tenant.logout') }}">
                    @csrf
                    <button type="submit"
                            class="block w-full bg-gray-100 text-gray-700 font-semibold py-3 px-6 rounded-xl hover:bg-gray-200 transition">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-sm text-gray-400 mt-6">
            Need help? Contact <a href="mailto:support@{{ config('app.base_domain') }}" class="text-indigo-600 hover:underline">support</a>
        </p>
    </div>
</body>
</html>
