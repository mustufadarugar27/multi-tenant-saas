<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Created — SaaS Platform</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased">

<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-green-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">

        <h1 class="text-3xl font-bold text-gray-900">You're all set!</h1>
        <p class="mt-3 text-gray-500 text-lg">
            Your workspace is being prepared. You'll receive a welcome email with your login link shortly.
        </p>

        @if($sessionId)
            <p class="mt-2 text-sm text-gray-400">Payment confirmed. Your subscription is now active.</p>
        @endif

        <div class="mt-8 rounded-xl bg-white border border-gray-100 shadow-sm p-6 text-left space-y-3">
            <h3 class="font-semibold text-gray-800">What happens next?</h3>
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start gap-2">
                    <svg class="h-4 w-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Check your inbox for your workspace login link.
                </li>
                <li class="flex items-start gap-2">
                    <svg class="h-4 w-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Invite your team from the admin dashboard.
                </li>
                <li class="flex items-start gap-2">
                    <svg class="h-4 w-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Create your first project and start tracking tasks.
                </li>
            </ul>
        </div>

        <p class="mt-6 text-sm text-gray-400">
            Questions? <a href="mailto:support@example.com" class="text-indigo-600 hover:underline">Contact support</a>
        </p>
    </div>
</div>

</body>
</html>
