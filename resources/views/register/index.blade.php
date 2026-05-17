<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Your Workspace — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html { scroll-behavior: smooth; }
        .plan-card { cursor: pointer; transition: border-color .15s, box-shadow .15s, transform .15s; }
        .plan-card:hover { transform: translateY(-2px); }
        .plan-card.selected { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(99,102,241,.2); }
        .step-done { background: #22c55e !important; color: #fff !important; }
        .step-active { background: #4f46e5 !important; color: #fff !important; }
        .step-pending { background: #e5e7eb !important; color: #9ca3af !important; }
        input:focus { outline: none; }
    </style>
</head>
<body class="min-h-full bg-gradient-to-br from-slate-50 via-white to-indigo-50 font-sans antialiased">

    {{-- Nav --}}
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <div class="h-7 w-7 rounded-lg bg-indigo-600 flex items-center justify-center">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
                <span class="font-bold text-gray-900">{{ config('app.name') }}</span>
            </a>
            <p class="text-sm text-gray-500">
                Already registered?
                <span class="text-gray-400 ml-1">Sign in via your workspace: <span class="font-mono text-xs text-indigo-500">yourslug.{{ config('app.base_domain') }}</span></span>
            </p>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- Alerts --}}
        @if ($cancelled)
        <div class="mb-6 flex items-start gap-3 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
            <svg class="h-4 w-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            Payment was cancelled. No charge was made — please try again.
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold mb-1">Please fix the following:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Step indicator --}}
        <div class="flex items-center justify-center mb-10">
            @foreach (['Choose Plan', 'Company Info', 'Admin Account'] as $i => $label)
            <div class="flex items-center">
                <div class="flex flex-col items-center">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold step-circle step-{{ $i === 0 ? 'active' : 'pending' }}"
                         data-step="{{ $i + 1 }}">{{ $i + 1 }}</div>
                    <span class="mt-1.5 text-xs font-medium step-label {{ $i === 0 ? 'text-indigo-600' : 'text-gray-400' }}"
                          data-step="{{ $i + 1 }}">{{ $label }}</span>
                </div>
                @if ($i < 2)
                <div class="w-20 sm:w-28 h-px bg-gray-200 mx-2 mb-4 step-connector" data-after="{{ $i + 1 }}"></div>
                @endif
            </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('register.store') }}" id="registration-form">
            @csrf
            <input type="hidden" name="billing_cycle" id="billing_cycle" value="monthly">
            <input type="hidden" name="plan_id"   id="plan_id"   value="{{ old('plan_id') }}">
            <input type="hidden" name="plan_slug" id="plan_slug" value="{{ old('plan_slug') }}">

            {{-- ─── STEP 1: Plan ─────────────────────────────────────────────────────── --}}
            <div id="step-1" class="step-panel">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Choose your plan</h1>
                    <p class="mt-2 text-gray-500">Start free, upgrade anytime. All plans include a 14-day trial.</p>
                </div>

                {{-- Billing toggle --}}
                <div class="flex items-center justify-center gap-3 mb-8">
                    <span class="text-sm font-medium text-gray-700" id="label-monthly">Monthly</span>
                    <button type="button" id="billing-toggle"
                        class="relative inline-flex h-6 w-11 rounded-full bg-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        role="switch" aria-checked="false">
                        <span class="translate-x-1 inline-block h-4 w-4 rounded-full bg-white shadow transform transition-transform mt-1"></span>
                    </button>
                    <span class="text-sm font-medium text-gray-700" id="label-yearly">
                        Yearly
                        <span class="ml-1 bg-emerald-100 text-emerald-700 text-xs font-semibold px-2 py-0.5 rounded-full">Save 17%</span>
                    </span>
                </div>

                @if ($plans->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 py-16 text-center">
                    <p class="text-gray-500 text-sm">No plans available yet.</p>
                    <p class="text-gray-400 text-xs mt-1">Run <code class="font-mono bg-gray-100 px-1 rounded">make artisan db:seed --class=PlanSeeder</code> to seed them.</p>
                </div>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-3">
                    @foreach ($plans as $plan)
                    <div class="plan-card rounded-2xl border-2 border-gray-200 bg-white relative
                            {{ $plan->is_default ? 'selected' : '' }}"
                         data-plan-id="{{ $plan->id }}"
                         data-plan-slug="{{ $plan->slug }}"
                         data-plan-name="{{ $plan->name }}"
                         data-price-monthly="{{ $plan->price_monthly }}"
                         data-price-yearly="{{ $plan->price_yearly }}"
                         data-is-free="{{ $plan->is_free ? '1' : '0' }}">

                        @if ($plan->is_default)
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 whitespace-nowrap">
                            <span class="bg-gradient-to-r from-violet-500 to-indigo-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                                Most Popular
                            </span>
                        </div>
                        @endif

                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>
                            @if ($plan->description)
                            <p class="mt-1 text-sm text-gray-500">{{ $plan->description }}</p>
                            @endif

                            <div class="mt-4 mb-5">
                                @if ($plan->is_free)
                                <div class="flex items-end gap-1">
                                    <span class="text-4xl font-extrabold text-gray-900">$0</span>
                                    <span class="text-gray-400 text-sm mb-1">/ forever</span>
                                </div>
                                @else
                                <div class="price-monthly">
                                    <div class="flex items-end gap-1">
                                        <span class="text-4xl font-extrabold text-gray-900">${{ number_format($plan->price_monthly, 0) }}</span>
                                        <span class="text-gray-400 text-sm mb-1">/ month</span>
                                    </div>
                                </div>
                                <div class="price-yearly hidden">
                                    <div class="flex items-end gap-1">
                                        <span class="text-4xl font-extrabold text-gray-900">${{ number_format($plan->price_yearly / 12, 0) }}</span>
                                        <span class="text-gray-400 text-sm mb-1">/ month</span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-0.5">${{ number_format($plan->price_yearly, 0) }} billed annually</p>
                                </div>
                                @endif
                            </div>

                            <ul class="space-y-2 mb-6 text-sm text-gray-600">
                                <li class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-indigo-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    @if ($plan->max_users) Up to {{ $plan->max_users }} team members @else Unlimited team members @endif
                                </li>
                                @if ($plan->storage_gb)
                                <li class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-indigo-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    {{ $plan->storage_gb }} GB storage
                                </li>
                                @endif
                                @foreach ($plan->features ?? [] as $feat)
                                <li class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-indigo-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    {{ $feat }}
                                </li>
                                @endforeach
                            </ul>

                            <button type="button"
                                class="plan-select-btn w-full rounded-xl py-2.5 text-sm font-semibold transition-colors
                                {{ $plan->is_default
                                    ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                                    : 'bg-gray-100 text-gray-700 hover:bg-indigo-600 hover:text-white' }}">
                                @if ($plan->is_free) Get started free
                                @elseif ($plan->slug === 'enterprise') Start enterprise trial
                                @else Start free trial
                                @endif
                            </button>
                        </div>

                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ─── STEP 2: Company ──────────────────────────────────────────────────── --}}
            <div id="step-2" class="step-panel hidden">
                <div class="max-w-lg mx-auto">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Your company</h2>
                        <p class="mt-2 text-gray-500">This becomes your workspace address.</p>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">

                        <div>
                            <label for="company_name" class="block text-sm font-semibold text-gray-700 mb-1.5">Company name</label>
                            <input id="company_name" name="company_name" type="text" required
                                   value="{{ old('company_name') }}"
                                   class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                   placeholder="Acme Corp" autocomplete="organization">
                            @error('company_name')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-semibold text-gray-700 mb-1.5">Workspace URL</label>
                            <div class="flex rounded-xl shadow-sm border border-gray-300 overflow-hidden focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-200 transition">
                                <input id="slug" name="slug" type="text" required
                                       value="{{ old('slug') }}"
                                       class="flex-1 border-0 px-4 py-2.5 text-sm focus:ring-0 focus:outline-none min-w-0"
                                       placeholder="acme-corp"
                                       pattern="[a-z0-9\-]+" title="Lowercase letters, numbers and hyphens only">
                                <span class="inline-flex items-center px-3 bg-gray-50 border-l border-gray-300 text-sm text-gray-500 font-medium whitespace-nowrap">
                                    .{{ config('app.base_domain') }}
                                </span>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-400">Lowercase letters, numbers, and hyphens only. This cannot be changed later.</p>
                            @error('slug')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3 pt-1">
                            <button type="button" id="back-to-1"
                                    class="flex-1 rounded-xl border border-gray-300 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                                ← Back
                            </button>
                            <button type="button" id="go-to-3"
                                    class="flex-1 rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                                Continue →
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── STEP 3: Admin Account ────────────────────────────────────────────── --}}
            <div id="step-3" class="step-panel hidden">
                <div class="max-w-lg mx-auto">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Admin account</h2>
                        <p class="mt-2 text-gray-500">You'll be the workspace owner.</p>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-5">

                        <div>
                            <label for="admin_name" class="block text-sm font-semibold text-gray-700 mb-1.5">Full name</label>
                            <input id="admin_name" name="name" type="text" required
                                   value="{{ old('name') }}"
                                   class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                   placeholder="Jane Smith" autocomplete="name">
                            @error('name')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Work email</label>
                            <input id="email" name="email" type="email" required
                                   value="{{ old('email') }}"
                                   class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                   placeholder="jane@acme.com" autocomplete="email">
                            @error('email')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                            <input id="password" name="password" type="password" required
                                   class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                   placeholder="At least 8 characters" autocomplete="new-password">
                            @error('password')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                   class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                   placeholder="Repeat password" autocomplete="new-password">
                        </div>

                        {{-- Order summary --}}
                        <div class="rounded-xl bg-indigo-50 border border-indigo-100 px-4 py-3.5 text-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-800" id="summary-plan">—</p>
                                    <p class="text-gray-500 text-xs mt-0.5" id="summary-workspace"></p>
                                </div>
                                <p class="font-bold text-indigo-700 text-base" id="summary-price">—</p>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-1">
                            <button type="button" id="back-to-2"
                                    class="flex-1 rounded-xl border border-gray-300 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                                ← Back
                            </button>
                            <button type="submit" id="submit-btn"
                                    class="flex-1 rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                                <span id="submit-label">Create workspace</span>
                            </button>
                        </div>

                        <p class="text-center text-xs text-gray-400">
                            By creating an account you agree to our Terms of Service and Privacy Policy.
                        </p>
                    </div>
                </div>
            </div>

        </form>
    </div>

    {{-- Creating overlay --}}
    <div id="creating-overlay" class="hidden fixed inset-0 z-50 bg-white/95 backdrop-blur-sm flex items-center justify-center">
        <div class="text-center px-6 space-y-6">
            <svg class="animate-spin h-14 w-14 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900">Setting up your workspace…</h2>
                <p class="mt-2 text-gray-500 text-sm">Creating your database and configuring your team environment.<br>This usually takes a few seconds.</p>
            </div>
            <div class="flex items-center justify-center gap-1.5">
                @foreach ([0, 150, 300] as $delay)
                <span class="h-2 w-2 rounded-full bg-indigo-500 animate-bounce" style="animation-delay:{{ $delay }}ms"></span>
                @endforeach
            </div>
        </div>
    </div>

    <script>
    (() => {
        const baseDomain = '{{ config('app.base_domain') }}';
        let isYearly = false;
        let selectedPlan = null;

        // ── helpers ─────────────────────────────────────────────────────────────
        const $ = id => document.getElementById(id);
        const show = step => {
            document.querySelectorAll('.step-panel').forEach(p => p.classList.add('hidden'));
            $('step-' + step).classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });

            document.querySelectorAll('.step-circle').forEach(el => {
                const n = +el.dataset.step;
                el.className = el.className.replace(/step-(done|active|pending)/g, '');
                el.classList.add(n < step ? 'step-done' : n === step ? 'step-active' : 'step-pending');
                el.innerHTML = n < step ? '✓' : n;
            });
            document.querySelectorAll('.step-label').forEach(el => {
                const n = +el.dataset.step;
                el.className = el.className.replace(/text-indigo-\S+|text-gray-\S+/g, '');
                el.classList.add(n === step ? 'text-indigo-600' : 'text-gray-400');
            });
        };

        const planPrice = plan => {
            if (!plan || plan.isFree) return 0;
            return isYearly ? plan.priceYearly : plan.priceMonthly;
        };

        const updateSummary = () => {
            if (!selectedPlan) return;
            const slug = $('slug').value || '…';
            $('summary-plan').textContent = selectedPlan.name + ' plan';
            $('summary-workspace').textContent = slug + '.' + baseDomain;
            if (selectedPlan.isFree) {
                $('summary-price').textContent = 'Free';
                $('submit-label').textContent = 'Create workspace';
            } else {
                const price = isYearly ? selectedPlan.priceYearly / 12 : selectedPlan.priceMonthly;
                $('summary-price').textContent = '$' + Math.round(price) + (isYearly ? '/mo (billed yearly)' : '/mo');
                $('submit-label').textContent = 'Continue to payment';
            }
        };

        // ── billing toggle ───────────────────────────────────────────────────────
        $('billing-toggle').addEventListener('click', function () {
            isYearly = !isYearly;
            this.setAttribute('aria-checked', isYearly);
            this.classList.toggle('bg-indigo-600', isYearly);
            this.classList.toggle('bg-gray-200', !isYearly);
            this.querySelector('span').classList.toggle('translate-x-6', isYearly);
            this.querySelector('span').classList.toggle('translate-x-1', !isYearly);
            $('billing_cycle').value = isYearly ? 'yearly' : 'monthly';

            document.querySelectorAll('.price-monthly').forEach(el => el.classList.toggle('hidden', isYearly));
            document.querySelectorAll('.price-yearly').forEach(el => el.classList.toggle('hidden', !isYearly));
        });

        // ── plan cards ───────────────────────────────────────────────────────────
        document.querySelectorAll('.plan-card').forEach(card => {
            const selectPlan = () => {
                selectedPlan = {
                    id:           card.dataset.planId,
                    slug:         card.dataset.planSlug,
                    name:         card.dataset.planName,
                    priceMonthly: parseFloat(card.dataset.priceMonthly),
                    priceYearly:  parseFloat(card.dataset.priceYearly),
                    isFree:       card.dataset.isFree === '1',
                };
                $('plan_id').value   = selectedPlan.id;
                $('plan_slug').value = selectedPlan.slug;

                document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');

                show(2);
            };

            card.addEventListener('click', selectPlan);
            card.querySelector('.plan-select-btn').addEventListener('click', e => {
                e.stopPropagation();
                selectPlan();
            });
        });

        // ── auto-slug ────────────────────────────────────────────────────────────
        $('company_name').addEventListener('input', e => {
            $('slug').value = e.target.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
        });

        // ── step navigation ──────────────────────────────────────────────────────
        $('go-to-3').addEventListener('click', () => {
            const company = $('company_name').value.trim();
            const slug    = $('slug').value.trim();

            if (!company) { $('company_name').focus(); return; }
            if (!slug)    { $('slug').focus(); return; }
            if (!/^[a-z0-9-]+$/.test(slug)) {
                $('slug').setCustomValidity('Lowercase letters, numbers and hyphens only.');
                $('slug').reportValidity();
                return;
            }
            $('slug').setCustomValidity('');

            updateSummary();
            show(3);
        });

        $('back-to-1').addEventListener('click', () => show(1));
        $('back-to-2').addEventListener('click', () => {
            updateSummary();
            show(2);
        });

        // Update summary when slug changes (user is on step 3 going back to fix slug)
        $('slug').addEventListener('input', updateSummary);

        // ── form submit ──────────────────────────────────────────────────────────
        $('registration-form').addEventListener('submit', () => {
            $('creating-overlay').classList.remove('hidden');
            $('submit-btn').disabled = true;
        });

        // ── restore state after validation error ─────────────────────────────────
        @if (old('plan_slug'))
            const matchedCard = document.querySelector('[data-plan-slug="{{ old('plan_slug') }}"]');
            if (matchedCard) {
                matchedCard.classList.add('selected');
                selectedPlan = {
                    id:           matchedCard.dataset.planId,
                    slug:         matchedCard.dataset.planSlug,
                    name:         matchedCard.dataset.planName,
                    priceMonthly: parseFloat(matchedCard.dataset.priceMonthly),
                    priceYearly:  parseFloat(matchedCard.dataset.priceYearly),
                    isFree:       matchedCard.dataset.isFree === '1',
                };
                $('plan_id').value   = selectedPlan.id;
                $('plan_slug').value = selectedPlan.slug;
            }
        @endif

        @if ($errors->has('company_name') || $errors->has('slug'))
            show(2);
        @elseif ($errors->has('name') || $errors->has('email') || $errors->has('password'))
            updateSummary();
            show(3);
        @elseif ($errors->has('plan_slug') || $errors->has('billing_cycle'))
            show(1);
        @endif
    })();
    </script>

</body>
</html>
