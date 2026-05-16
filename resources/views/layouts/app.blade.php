<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ tenant('name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased">

<div class="min-h-full">
    {{-- Navbar --}}
    <nav class="bg-indigo-600">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-8">
                    <span class="text-white font-bold text-lg tracking-tight">
                        {{ ucfirst(tenant('name')) }}
                    </span>
                    <div class="hidden md:flex items-center gap-1">
                        <a href="{{ route('tenant.projects.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium transition
                                  {{ request()->routeIs('tenant.projects.*')
                                     ? 'bg-indigo-700 text-white'
                                     : 'text-indigo-100 hover:bg-indigo-500 hover:text-white' }}">
                            Projects
                        </a>
                        <a href="{{ route('tenant.tasks.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium transition
                                  {{ request()->routeIs('tenant.tasks.*')
                                     ? 'bg-indigo-700 text-white'
                                     : 'text-indigo-100 hover:bg-indigo-500 hover:text-white' }}">
                            Tasks
                        </a>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    @can('create', \App\Models\User::class)
                        <a href="{{ route('tenant.users.create') }}"
                           class="inline-flex items-center gap-1.5 rounded-md bg-indigo-500 px-3 py-1.5
                                  text-sm font-medium text-white hover:bg-indigo-400 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/>
                            </svg>
                            Create User
                        </a>
                    @endcan

                    {{-- Bell notification --}}
                    <div class="relative" id="notif-wrapper">
                        <button id="notif-btn" type="button"
                                class="relative p-1.5 rounded-full text-indigo-100 hover:text-white hover:bg-indigo-500 transition focus:outline-none"
                                aria-label="Notifications">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                            </svg>
                            <span id="notif-badge"
                                  class="absolute -top-0.5 -right-0.5 hidden h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white leading-none">
                                0
                            </span>
                        </button>

                        {{-- Dropdown panel --}}
                        <div id="notif-panel"
                             class="hidden absolute right-0 mt-2 w-80 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black/10 z-50
                                    transition-all duration-200 ease-out"
                             style="transform-origin: top right;">
                            <ul id="notif-list" class="max-h-80 overflow-y-auto divide-y divide-gray-50 rounded-xl">
                                <li class="px-4 py-6 text-center text-sm text-gray-400" id="notif-empty">
                                    No notifications yet
                                </li>
                            </ul>
                        </div>
                    </div>

                    <a href="{{ route('tenant.profile') }}"
                       class="text-indigo-100 hover:text-white text-sm font-medium transition">
                        {{ auth()->user()->name }}
                    </a>
                    <form method="POST" action="{{ route('tenant.logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-indigo-100 hover:text-white text-sm font-medium transition">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- Page header --}}
    @hasSection('header')
    <header class="bg-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            @yield('header')
        </div>
    </header>
    @endif

    {{-- Flash messages --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-4 space-y-2">
        @if (session('success'))
            <div class="flex items-center gap-3 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                <svg class="h-5 w-5 shrink-0 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-3 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                <svg class="h-5 w-5 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif
    </div>

    {{-- Main content --}}
    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @yield('content')
    </main>
</div>

<script>
(function () {
    const btn      = document.getElementById('notif-btn');
    const panel    = document.getElementById('notif-panel');
    const badge    = document.getElementById('notif-badge');
    const list     = document.getElementById('notif-list');
    const empty    = document.getElementById('notif-empty');

    const API_INDEX = '{{ route("tenant.notifications.index") }}';

    let open = false;
    let notifications = [];

    function formatDate(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) +
               ' · ' + d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
    }

    function labelFor(n) {
        const data = n.data || {};
        const type = data.type || '';
        if (type === 'task_assigned') {
            return '<span class="font-medium text-gray-800">Task assigned:</span> ' +
                   escHtml(data.task_title || 'Untitled') +
                   (data.assigned_by ? ' <span class="text-gray-400">by ' + escHtml(data.assigned_by.name) + '</span>' : '');
        }
        if (type === 'comment_added') {
            return '<span class="font-medium text-gray-800">New comment</span> on ' +
                   escHtml(data.task_title || 'a task') +
                   (data.commenter ? ' <span class="text-gray-400">by ' + escHtml(data.commenter) + '</span>' : '');
        }
        if (type === 'subscription_expired') {
            return '<span class="font-medium text-gray-800">Subscription expired</span>';
        }
        return '<span class="text-gray-700">' + escHtml(type.replace(/_/g, ' ')) + '</span>';
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function renderList() {
        if (!notifications.length) {
            empty.classList.remove('hidden');
            return;
        }
        empty.classList.add('hidden');

        const items = notifications.map(n => {
            const unread = !n.read_at;
            return `<li class="flex gap-3 px-4 py-3 ${unread ? 'bg-indigo-50/60' : ''}">
                        <span class="mt-1 flex-shrink-0 h-2 w-2 rounded-full ${unread ? 'bg-indigo-500' : 'bg-transparent'}"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm leading-snug">${labelFor(n)}</p>
                            <p class="text-xs text-gray-400 mt-0.5">${formatDate(n.created_at)}</p>
                        </div>
                    </li>`;
        });
        list.innerHTML = items.join('') + (empty.outerHTML);
        document.getElementById('notif-empty').classList.add('hidden');
    }

    function updateBadge(count) {
        if (count > 0) {
            badge.classList.remove('hidden');
            badge.classList.add('inline-flex');
            badge.textContent = count > 9 ? '9+' : count;
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('inline-flex');
        }
    }

    async function loadNotifications() {
        try {
            const res = await fetch(API_INDEX, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            notifications = json.notifications || [];
            updateBadge(json.unread_count || 0);
            renderList();
        } catch (_) {}
    }


    function showPanel() {
        panel.classList.remove('hidden');
        panel.style.opacity = '0';
        panel.style.transform = 'scaleY(0.95) translateY(-4px)';
        requestAnimationFrame(() => {
            panel.style.transition = 'opacity 180ms ease, transform 180ms ease';
            panel.style.opacity = '1';
            panel.style.transform = 'scaleY(1) translateY(0)';
        });
        open = true;
    }

    function hidePanel() {
        panel.style.opacity = '0';
        panel.style.transform = 'scaleY(0.95) translateY(-4px)';
        setTimeout(() => panel.classList.add('hidden'), 180);
        open = false;
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (open) { hidePanel(); } else { loadNotifications(); showPanel(); }
    });

    document.addEventListener('click', function (e) {
        if (open && !panel.contains(e.target) && e.target !== btn) { hidePanel(); }
    });

    // Poll for new notifications every 60s
    loadNotifications();
    setInterval(loadNotifications, 60000);
})();
</script>
</body>
</html>
