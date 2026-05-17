<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Project Management for Growing Teams</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700,800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: 'Figtree', sans-serif; background: #fff; color: #111827; }
            .hidden { display: none !important; }
        </style>
    @endif

    <style>
        html { scroll-behavior: smooth; }

        .hero-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 55%, #312e81 100%);
            position: relative;
            overflow: hidden;
        }
        .hero-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 1px 1px, rgba(99,102,241,.18) 1px, transparent 0);
            background-size: 30px 30px;
            pointer-events: none;
        }

        .gradient-text {
            background: linear-gradient(135deg, #a5b4fc 0%, #e879f9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp .55s ease both; }
        .d1 { animation-delay: .08s; }
        .d2 { animation-delay: .18s; }
        .d3 { animation-delay: .28s; }
        .d4 { animation-delay: .38s; }

        .mockup-shell {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        .mockup-row {
            background: rgba(255,255,255,.07);
            border-radius: 6px;
        }
        .mockup-row-active {
            background: rgba(99,102,241,.35);
            border-radius: 6px;
        }

        .feature-card:hover { transform: translateY(-3px); }
        .feature-card { transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }

        .step-connector {
            position: absolute;
            top: 28px;
            left: calc(33.33% + .5rem);
            right: calc(33.33% + .5rem);
            height: 1px;
            background: linear-gradient(90deg, #c7d2fe, #ddd6fe, #c7d2fe);
        }

        .billing-toggle-btn {
            padding: .375rem 1rem;
            border-radius: 9999px;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .18s ease;
            border: none;
            background: transparent;
        }
        .billing-toggle-btn.active {
            background: #fff;
            color: #4338ca;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }
        .billing-toggle-btn:not(.active) { color: #6b7280; }
        .billing-toggle-btn:not(.active):hover { color: #374151; }

        /* Mobile menu transition */
        #mobile-menu {
            transition: max-height .25s ease, opacity .25s ease;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }
        #mobile-menu.open {
            max-height: 300px;
            opacity: 1;
        }
    </style>
</head>
<body class="antialiased" style="font-family:'Figtree',sans-serif;">

{{-- ═══════════════════════════════════════ NAV ══════════════════════════════════════════ --}}
<nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

        <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
            <div class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center shadow-sm group-hover:bg-indigo-700 transition-colors">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
            </div>
            <span class="font-bold text-gray-900 text-lg tracking-tight">{{ config('app.name') }}</span>
        </a>

        <div class="flex items-center gap-5">
            <a href="#features" class="hidden sm:block text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">Features</a>
            <a href="#how-it-works" class="hidden sm:block text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">How it works</a>
            <a href="#pricing" class="hidden sm:block text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">Pricing</a>
            <a href="{{ route('register.index') }}"
               class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-colors shadow-sm">
                Get Started
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
            </a>
            {{-- Hamburger (mobile only) --}}
            <button id="mobile-menu-btn" class="sm:hidden p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors" aria-label="Toggle menu">
                <svg id="icon-open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg id="icon-close" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

    </div>

    {{-- Mobile menu --}}
    <div id="mobile-menu">
        <div class="max-w-6xl mx-auto px-4 pb-4 flex flex-col gap-1">
            <a href="#features" class="px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors" onclick="closeMobileMenu()">Features</a>
            <a href="#how-it-works" class="px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors" onclick="closeMobileMenu()">How it works</a>
            <a href="#pricing" class="px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors" onclick="closeMobileMenu()">Pricing</a>
            <a href="{{ route('register.index') }}"
               class="mt-1 flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-colors shadow-sm">
                Get Started
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        </div>
    </div>
</nav>

{{-- ═══════════════════════════════════════ HERO ═════════════════════════════════════════ --}}
<section class="hero-bg pt-20 pb-24 sm:pt-28 sm:pb-32">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-14 lg:items-center">

            {{-- Left: copy --}}
            <div>
                <div class="fade-up d1 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-500/20 border border-indigo-400/25 mb-6">
                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-400 animate-pulse flex-shrink-0"></span>
                    <span class="text-indigo-200 text-xs font-semibold tracking-wide uppercase">Multi-tenant SaaS platform</span>
                </div>

                <h1 class="fade-up d2 text-4xl sm:text-5xl lg:text-[3.4rem] font-extrabold text-white leading-[1.1] tracking-tight">
                    Manage projects.<br>
                    <span class="gradient-text">Move faster.</span>
                </h1>

                <p class="fade-up d3 mt-5 text-[1.05rem] text-indigo-200 max-w-md leading-relaxed">
                    Give your company a private, isolated workspace to plan work, track tasks, and collaborate in real time — from day one.
                </p>

                <div class="fade-up d4 mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('register.index') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white text-indigo-700 font-bold text-sm hover:bg-indigo-50 transition-colors shadow-lg shadow-indigo-900/25">
                        Start Free Trial
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                    <a href="#pricing"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-white/20 text-white font-semibold text-sm hover:border-white/40 hover:bg-white/5 transition-colors">
                        View Plans
                    </a>
                </div>

                <div class="fade-up d4 mt-9 flex flex-wrap gap-x-6 gap-y-2">
                    @foreach (['14-day free trial', 'No credit card required', 'Cancel anytime'] as $perk)
                    <span class="flex items-center gap-1.5 text-sm text-indigo-300">
                        <svg class="h-3.5 w-3.5 text-indigo-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ $perk }}
                    </span>
                    @endforeach
                </div>
            </div>

            {{-- Right: dashboard mockup --}}
            <div class="hidden lg:block">
                <div class="mockup-shell p-5 max-w-[22rem] ml-auto">
                    {{-- Title bar dots --}}
                    <div class="flex items-center gap-1.5 mb-4">
                        <div class="h-2.5 w-2.5 rounded-full bg-rose-400/60"></div>
                        <div class="h-2.5 w-2.5 rounded-full bg-amber-400/60"></div>
                        <div class="h-2.5 w-2.5 rounded-full bg-emerald-400/60"></div>
                        <div class="ml-3 h-3 w-28 rounded bg-white/10"></div>
                    </div>

                    {{-- Layout: sidebar + content --}}
                    <div class="flex gap-3">
                        {{-- Sidebar --}}
                        <div class="w-28 space-y-1.5 pt-1">
                            <div class="mockup-row-active h-6 flex items-center px-2">
                                <div class="h-1.5 w-16 rounded bg-indigo-200/50"></div>
                            </div>
                            @foreach (['w-10','w-14','w-11','w-16','w-10'] as $w)
                            <div class="h-5 flex items-center px-2">
                                <div class="h-1.5 {{ $w }} rounded bg-white/15"></div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Task list --}}
                        <div class="flex-1 space-y-2">
                            <div class="h-5 flex items-center">
                                <div class="h-2 w-20 rounded bg-white/25"></div>
                            </div>
                            @foreach ([
                                ['w-4/5', 'bg-emerald-400/70'],
                                ['w-1/2', 'bg-amber-400/70'],
                                ['w-3/4', 'bg-indigo-400/70'],
                                ['w-2/3', 'bg-emerald-400/70'],
                                ['w-5/6', 'bg-violet-400/70'],
                            ] as $row)
                            <div class="mockup-row h-8 flex items-center justify-between px-2.5">
                                <div class="h-1.5 {{ $row[0] }} rounded bg-white/25"></div>
                                <div class="h-3 w-3 rounded-full {{ $row[1] }} flex-shrink-0"></div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Bottom stat bars --}}
                    <div class="mt-3 grid grid-cols-3 gap-1.5">
                        @foreach (['bg-indigo-500/35','bg-violet-500/30','bg-indigo-500/20'] as $bg)
                        <div class="h-6 {{ $bg }} rounded flex items-center justify-center">
                            <div class="h-1.5 w-8 rounded bg-white/20"></div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════ STATS ════════════════════════════════════════ --}}
<div class="bg-gray-50 border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 text-center divide-y sm:divide-y-0 sm:divide-x divide-gray-200">
            @foreach ([['500+','Companies using it'],['99.9%','Uptime SLA'],['100%','Data isolation']] as $s)
            <div class="py-4 sm:py-0 first:pt-0 last:pb-0">
                <dt class="text-3xl font-extrabold text-indigo-600">{{ $s[0] }}</dt>
                <dd class="mt-1 text-sm font-medium text-gray-500">{{ $s[1] }}</dd>
            </div>
            @endforeach
        </dl>
    </div>
</div>

{{-- ═══════════════════════════════════════ FEATURES ════════════════════════════════════ --}}
<section id="features" class="py-20 sm:py-28 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-2xl mx-auto mb-14">
            <p class="text-indigo-600 font-semibold text-xs uppercase tracking-[.15em] mb-3">Everything you need</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Built for teams that ship</h2>
            <p class="mt-4 text-gray-500 text-lg leading-relaxed">
                Your company gets a private, isolated workspace with all the tools to plan, track, and deliver — without the overhead.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            @foreach ([
                [
                    'path'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                    'bg'    => 'bg-indigo-50',
                    'icon'  => 'text-indigo-600',
                    'title' => 'Project & Task Management',
                    'desc'  => 'Organise work into projects, break them into tasks, and track status through a clear lifecycle — To Do, In Progress, In Review, Done.',
                ],
                [
                    'path'  => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                    'bg'    => 'bg-violet-50',
                    'icon'  => 'text-violet-600',
                    'title' => 'Real-time Collaboration',
                    'desc'  => 'Task updates, comments, and assignments appear instantly across your whole team via live WebSocket connections powered by Laravel Reverb.',
                ],
                [
                    'path'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'bg'    => 'bg-indigo-50',
                    'icon'  => 'text-indigo-600',
                    'title' => 'Roles & Permissions',
                    'desc'  => 'Granular role-based access control. Assign super admins, managers, and members with exactly the permissions each person needs — nothing more.',
                ],
            ] as $feature)
            <div class="feature-card p-7 rounded-2xl border border-gray-100 bg-white hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-50/60">
                <div class="h-11 w-11 rounded-xl {{ $feature['bg'] }} flex items-center justify-center mb-5">
                    <svg class="h-5 w-5 {{ $feature['icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['path'] }}"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base mb-2">{{ $feature['title'] }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $feature['desc'] }}</p>
            </div>
            @endforeach
        </div>

    </div>
</section>

{{-- ═══════════════════════════════════════ HOW IT WORKS ═════════════════════════════════ --}}
<section id="how-it-works" class="py-20 sm:py-28 bg-gray-50 border-y border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-xl mx-auto mb-14">
            <p class="text-indigo-600 font-semibold text-xs uppercase tracking-[.15em] mb-3">Simple by design</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Up and running in minutes</h2>
            <p class="mt-4 text-gray-500 text-lg">No complex setup. No IT team required.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 sm:gap-10 relative">
            <div class="step-connector hidden sm:block"></div>

            @foreach ([
                ['01', 'Register your company',    'Pick a plan, choose your workspace subdomain, and create your admin account. The whole thing takes under two minutes.'],
                ['02', 'Invite your team',          'Add team members and assign roles — manager, member, or admin. Everyone gets instant access to the shared workspace.'],
                ['03', 'Start managing projects',   'Create projects, add tasks, set priorities, attach files, and watch your team collaborate live.'],
            ] as $step)
            <div class="flex sm:block items-start sm:text-center gap-5 sm:gap-0 relative">
                <div class="h-14 w-14 flex-shrink-0 rounded-full bg-indigo-600 text-white font-extrabold text-lg flex items-center justify-center sm:mx-auto mb-0 sm:mb-5 shadow-md shadow-indigo-200 relative z-10">
                    {{ $step[0] }}
                </div>
                <div class="pt-1 sm:pt-0">
                    <h3 class="font-bold text-gray-900 mb-2">{{ $step[1] }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed sm:max-w-xs sm:mx-auto">{{ $step[2] }}</p>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</section>

{{-- ═══════════════════════════════════════ PRICING ══════════════════════════════════════ --}}
<section id="pricing" class="py-20 sm:py-28 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-xl mx-auto mb-12">
            <p class="text-indigo-600 font-semibold text-xs uppercase tracking-[.15em] mb-3">Pricing</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Simple, transparent pricing</h2>
            <p class="mt-4 text-gray-500">Start free, scale when you need to. No hidden fees.</p>

            {{-- Billing toggle --}}
            <div class="mt-7 inline-flex items-center bg-gray-100 rounded-full p-1 gap-1">
                <button id="btn-monthly" class="billing-toggle-btn active" onclick="setBilling('monthly')">
                    Monthly
                </button>
                <button id="btn-yearly" class="billing-toggle-btn" onclick="setBilling('yearly')">
                    Yearly
                    <span class="ml-1.5 text-xs font-bold text-emerald-600">Save 17%</span>
                </button>
            </div>
        </div>

        @if ($plans->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 items-start">
            @foreach ($plans as $i => $plan)
            @php $highlight = $i === 1; @endphp
            <div class="rounded-2xl border-2 p-6 sm:p-7 flex flex-col relative transition-all duration-200
                {{ $highlight
                    ? 'border-indigo-600 bg-indigo-600 shadow-2xl shadow-indigo-200 mt-0 sm:mt-0'
                    : 'border-gray-200 bg-white hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-50' }}">

                @if ($highlight)
                <div class="flex justify-center mb-4 sm:mb-0 sm:absolute sm:-top-4 sm:left-1/2 sm:-translate-x-1/2">
                    <span class="bg-gradient-to-r from-violet-500 to-indigo-500 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow-md whitespace-nowrap">
                        Most Popular
                    </span>
                </div>
                @endif

                {{-- Plan name --}}
                <h3 class="font-extrabold text-lg {{ $highlight ? 'text-white' : 'text-gray-900' }}">
                    {{ $plan->name }}
                </h3>
                @if ($plan->description)
                <p class="mt-1 text-sm {{ $highlight ? 'text-indigo-200' : 'text-gray-500' }}">{{ $plan->description }}</p>
                @endif

                {{-- Price --}}
                <div class="mt-5 mb-6 min-h-[4.5rem]">
                    @if ($plan->is_free)
                        <div class="flex items-end gap-1">
                            <span class="text-5xl font-extrabold {{ $highlight ? 'text-white' : 'text-gray-900' }}">$0</span>
                            <span class="text-sm {{ $highlight ? 'text-indigo-200' : 'text-gray-400' }} mb-1.5">/ forever</span>
                        </div>
                    @else
                        <div class="price-monthly">
                            <div class="flex items-end gap-1">
                                <span class="text-5xl font-extrabold {{ $highlight ? 'text-white' : 'text-gray-900' }}">${{ number_format($plan->price_monthly, 0) }}</span>
                                <span class="text-sm {{ $highlight ? 'text-indigo-200' : 'text-gray-400' }} mb-1.5">/ month</span>
                            </div>
                        </div>
                        <div class="price-yearly hidden">
                            <div class="flex items-end gap-1">
                                <span class="text-5xl font-extrabold {{ $highlight ? 'text-white' : 'text-gray-900' }}">${{ number_format($plan->price_yearly / 12, 0) }}</span>
                                <span class="text-sm {{ $highlight ? 'text-indigo-200' : 'text-gray-400' }} mb-1.5">/ month</span>
                            </div>
                            <p class="text-xs mt-0.5 {{ $highlight ? 'text-indigo-200' : 'text-gray-400' }}">
                                ${{ number_format($plan->price_yearly, 0) }} billed annually
                            </p>
                        </div>
                    @endif
                </div>

                {{-- CTA --}}
                <a href="{{ route('register.index') }}"
                   class="block text-center py-3 rounded-xl text-sm font-bold transition-colors mb-7
                    {{ $highlight
                        ? 'bg-white text-indigo-700 hover:bg-indigo-50'
                        : 'bg-indigo-600 text-white hover:bg-indigo-700' }}">
                    {{ $plan->is_free ? 'Get started free' : 'Start free trial' }}
                </a>

                {{-- Feature list --}}
                <ul class="space-y-3">
                    @php
                        $lineColor = $highlight ? 'text-indigo-100' : 'text-gray-600';
                        $checkColor = $highlight ? 'text-indigo-200' : 'text-indigo-500';
                    @endphp

                    @if ($plan->max_users)
                    <li class="flex items-start gap-2.5 text-sm {{ $lineColor }}">
                        <svg class="h-4 w-4 mt-0.5 flex-shrink-0 {{ $checkColor }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Up to {{ $plan->max_users }} team members
                    </li>
                    @else
                    <li class="flex items-start gap-2.5 text-sm {{ $lineColor }}">
                        <svg class="h-4 w-4 mt-0.5 flex-shrink-0 {{ $checkColor }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Unlimited team members
                    </li>
                    @endif

                    @if ($plan->storage_gb)
                    <li class="flex items-start gap-2.5 text-sm {{ $lineColor }}">
                        <svg class="h-4 w-4 mt-0.5 flex-shrink-0 {{ $checkColor }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ $plan->storage_gb }} GB file storage
                    </li>
                    @endif

                    @foreach ($plan->features ?? [] as $feat)
                    <li class="flex items-start gap-2.5 text-sm {{ $lineColor }}">
                        <svg class="h-4 w-4 mt-0.5 flex-shrink-0 {{ $checkColor }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ $feat }}
                    </li>
                    @endforeach
                </ul>

            </div>
            @endforeach
        </div>
        @else
        <p class="text-center text-gray-400 text-sm">Plans loading… run <code class="font-mono bg-gray-100 px-1.5 py-0.5 rounded">make artisan db:seed --class=PlanSeeder</code> to populate them.</p>
        @endif

    </div>
</section>

{{-- ═══════════════════════════════════════ CTA BANNER ═══════════════════════════════════ --}}
<section class="hero-bg py-16 sm:py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
            Ready to get your team organised?
        </h2>
        <p class="mt-4 text-indigo-200 text-lg">Create your workspace in under two minutes. No credit card required.</p>
        <a href="{{ route('register.index') }}"
           class="mt-8 inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-white text-indigo-700 font-bold text-sm hover:bg-indigo-50 transition-colors shadow-xl shadow-indigo-900/30">
            Create your workspace
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
            </svg>
        </a>
    </div>
</section>

{{-- ═══════════════════════════════════════ FOOTER ═══════════════════════════════════════ --}}
<footer class="bg-white border-t border-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <div class="h-6 w-6 rounded bg-indigo-600 flex items-center justify-center">
                <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
            </div>
            <span class="font-bold text-gray-800 text-sm">{{ config('app.name') }}</span>
        </div>
        <p class="text-xs text-gray-400">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</footer>

<script>
    function setBilling(cycle) {
        const isYearly = cycle === 'yearly';

        document.querySelectorAll('.price-monthly').forEach(el => el.classList.toggle('hidden', isYearly));
        document.querySelectorAll('.price-yearly').forEach(el => el.classList.toggle('hidden', !isYearly));

        document.getElementById('btn-monthly').classList.toggle('active', !isYearly);
        document.getElementById('btn-yearly').classList.toggle('active', isYearly);
    }

    (function () {
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('icon-open');
        const iconClose = document.getElementById('icon-close');
        let open = false;

        btn.addEventListener('click', function () {
            open = !open;
            menu.classList.toggle('open', open);
            iconOpen.classList.toggle('hidden', open);
            iconClose.classList.toggle('hidden', !open);
        });

        // Close on resize to sm+
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 640 && open) closeMobileMenu();
        });
    })();

    function closeMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('icon-open');
        const iconClose = document.getElementById('icon-close');
        menu.classList.remove('open');
        iconOpen.classList.remove('hidden');
        iconClose.classList.add('hidden');
    }
</script>

</body>
</html>
