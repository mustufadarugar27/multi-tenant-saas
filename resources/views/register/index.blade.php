<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Your Company — SaaS Platform</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased">

    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">

        {{-- Header --}}
        <nav class="bg-white/80 backdrop-blur border-b border-gray-100 sticky top-0 z-50">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="font-bold text-gray-900 text-lg">SaaS Platform</span>
                </div>
                <p class="text-sm text-gray-500">Already have an account? <a href="#"
                        class="text-indigo-600 font-medium hover:underline">Sign in</a></p>
            </div>
        </nav>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            @if ($cancelled)
                <div class="mb-6 rounded-lg bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-800">
                    Payment was cancelled. Please try again.
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Step indicator --}}
            <div class="mb-10 flex items-center justify-center gap-0" id="step-indicator">
                @foreach (['Choose Plan', 'Company Info', 'Admin Account'] as $i => $label)
                    <div class="flex items-center">
                        <div class="flex flex-col items-center">
                            <div class="step-circle w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold transition-all
                            {{ $i === 0 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}"
                                data-step="{{ $i + 1 }}">
                                {{ $i + 1 }}
                            </div>
                            <span
                                class="mt-1 text-xs font-medium {{ $i === 0 ? 'text-indigo-600' : 'text-gray-400' }} step-label"
                                data-step="{{ $i + 1 }}">{{ $label }}</span>
                        </div>
                        @if ($i < 2)
                            <div class="w-24 sm:w-32 h-0.5 bg-gray-200 mx-2 step-line" data-after="{{ $i + 1 }}">
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('register.store') }}" id="registration-form">
                @csrf

                {{-- STEP 1: Plan Selection --}}
                <div id="step-1" class="step-panel">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-900">Choose your plan</h1>
                        <p class="mt-2 text-gray-500">Start free, upgrade anytime.</p>
                    </div>

                    {{-- Billing Toggle --}}
                    <div class="flex items-center justify-center gap-3 mb-8">
                        <span class="text-sm font-medium text-gray-700">Monthly</span>
                        <button type="button" id="billing-toggle"
                            class="relative inline-flex h-6 w-11 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 bg-gray-200"
                            role="switch" aria-checked="false">
                            <span
                                class="translate-x-1 inline-block h-4 w-4 rounded-full bg-white shadow transform transition-transform mt-1"></span>
                        </button>
                        <span class="text-sm font-medium text-gray-700">Yearly
                            <span
                                class="ml-1 bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">Save
                                20%</span>
                        </span>
                    </div>

                    <input type="hidden" name="billing_cycle" id="billing_cycle" value="monthly">
                    <input type="hidden" name="plan_id" id="plan_id" value="">
                    <input type="hidden" name="plan_slug" id="plan_slug" value="">

                    <div class="grid grid-cols-1 lg:grid-cols-3">
                        @foreach ($plans as $plan)
                            <div class="plan-card relative rounded-2xl border-2 cursor-pointer transition-all hover:shadow-lg
                            {{ $plan->slug === 'professional' ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-indigo-300' }}"
                                data-plan-id="{{ $plan->id }}" data-plan-slug="{{ $plan->slug }}"
                                data-price-monthly="{{ $plan->price_monthly }}"
                                data-price-yearly="{{ $plan->price_yearly }}"
                                data-is-free="{{ $plan->is_free ? '1' : '0' }}">

                                @if ($plan->slug === 'professional')
                                    <div
                                        class="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                                        Most Popular</div>
                                @endif

                                <div class="p-6">
                                    <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>
                                    <p class="mt-1 text-sm text-gray-500">{{ $plan->description }}</p>

                                    <div class="mt-4">
                                        <span class="plan-price text-4xl font-bold text-gray-900">
                                            @if ($plan->is_free)
                                                Free
                                            @else
                                                ${{ number_format((float) $plan->price_monthly, 0) }}
                                            @endif
                                        </span>
                                        @if (!$plan->is_free)
                                            <span class="text-gray-500 text-sm">/mo</span>
                                        @endif
                                    </div>

                                    <ul class="mt-5 space-y-2">
                                        @foreach ($plan->features ?? [] as $feature)
                                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                                <svg class="h-4 w-4 text-green-500 flex-shrink-0" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                {{ $feature }}
                                            </li>
                                        @endforeach
                                        <li class="flex items-center gap-2 text-sm text-gray-600">
                                            <svg class="h-4 w-4 text-green-500 flex-shrink-0" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            Up to {{ $plan->max_users }} users
                                        </li>
                                        <li class="flex items-center gap-2 text-sm text-gray-600">
                                            <svg class="h-4 w-4 text-green-500 flex-shrink-0" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            {{ $plan->storage_gb }}GB storage
                                        </li>
                                    </ul>

                                    <button type="button"
                                        class="plan-select-btn mt-6 w-full rounded-lg py-2.5 text-sm font-semibold transition
                                        {{ $plan->slug === 'professional'
                                            ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                                            : 'bg-gray-100 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}"
                                        data-plan-id="{{ $plan->id }}">
                                        Get started
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- STEP 2: Company Info --}}
                <div id="step-2" class="step-panel hidden">
                    <div class="max-w-xl mx-auto">
                        <div class="text-center mb-8">
                            <h2 class="text-3xl font-bold text-gray-900">Your company</h2>
                            <p class="mt-2 text-gray-500">This becomes your workspace URL.</p>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-5">
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700">Company
                                    name</label>
                                <input id="company_name" name="company_name" type="text" required
                                    value="{{ old('company_name') }}"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                                    placeholder="Acme Corp">
                                @error('company_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700">Workspace
                                    URL</label>
                                <div class="mt-1 flex rounded-lg shadow-sm">
                                    <span
                                        class="inline-flex items-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">
                                        app.example.com/
                                    </span>
                                    <input id="slug" name="slug" type="text" required
                                        value="{{ old('slug') }}"
                                        class="flex-1 rounded-r-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                                        placeholder="acme-corp">
                                </div>
                                <p class="mt-1 text-xs text-gray-400">Lowercase letters, numbers, and hyphens only.</p>
                                @error('slug')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="button" id="back-to-1"
                                    class="flex-1 rounded-lg border border-gray-300 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                                    Back
                                </button>
                                <button type="button" id="go-to-3"
                                    class="flex-1 rounded-lg bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                                    Continue
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STEP 3: Admin Account --}}
                <div id="step-3" class="step-panel hidden">
                    <div class="max-w-xl mx-auto">
                        <div class="text-center mb-8">
                            <h2 class="text-3xl font-bold text-gray-900">Admin account</h2>
                            <p class="mt-2 text-gray-500">You'll be the workspace administrator.</p>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-5">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Full
                                    name</label>
                                <input id="name" name="name" type="text" required
                                    value="{{ old('name') }}"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                                    placeholder="Jane Smith">
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Work
                                    email</label>
                                <input id="email" name="email" type="email" required
                                    value="{{ old('email') }}"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                                    placeholder="jane@acme.com">
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <input id="password" name="password" type="password" required
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                                    placeholder="Min 8 chars, mixed case + numbers">
                                @error('password')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation"
                                    class="block text-sm font-medium text-gray-700">Confirm password</label>
                                <input id="password_confirmation" name="password_confirmation" type="password"
                                    required
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition">
                            </div>

                            {{-- Summary --}}
                            <div id="order-summary"
                                class="rounded-lg bg-indigo-50 border border-indigo-100 px-4 py-3 text-sm">
                                <div class="flex justify-between font-medium text-gray-700">
                                    <span id="summary-plan">Plan</span>
                                    <span id="summary-price">Free</span>
                                </div>
                                <div class="mt-1 flex justify-between text-gray-500">
                                    <span id="summary-cycle"></span>
                                </div>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="button" id="back-to-2"
                                    class="flex-1 rounded-lg border border-gray-300 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                                    Back
                                </button>
                                <button type="submit"
                                    class="flex-1 rounded-lg bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                                    <span id="submit-label">Create account</span>
                                </button>
                            </div>

                            <p class="text-center text-xs text-gray-400">
                                By continuing you agree to our Terms of Service and Privacy Policy.
                            </p>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Loading overlay --}}
    <div id="creating-overlay"
        class="hidden fixed inset-0 z-50 bg-white/90 backdrop-blur-sm flex flex-col items-center justify-center">
        <div class="text-center space-y-5 px-6">
            <div class="relative mx-auto h-20 w-20">
                <svg class="animate-spin h-20 w-20 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-90" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Your website is being created…</h2>
                <p class="mt-2 text-gray-500 text-sm">We're setting up your workspace. This usually takes a few seconds.</p>
            </div>
            <div class="flex items-center justify-center gap-1.5 text-xs text-gray-400" id="creating-steps">
                <span class="inline-block h-2 w-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay:0ms"></span>
                <span class="inline-block h-2 w-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay:150ms"></span>
                <span class="inline-block h-2 w-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay:300ms"></span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let currentStep = 1;
            let isYearly = false;
            let selectedPlanId = '';
            let selectedPlanName = '';
            let selectedPlanMonthly = 0;
            let selectedPlanYearly = 0;
            let selectedPlanIsFree = true;

            const show = (step) => {
                document.querySelectorAll('.step-panel').forEach(p => p.classList.add('hidden'));
                document.getElementById('step-' + step).classList.remove('hidden');

                document.querySelectorAll('.step-circle').forEach(el => {
                    const s = parseInt(el.dataset.step);
                    el.className = el.className.replace(/bg-\S+|text-\S+/g, '').trim();
                    if (s < step) {
                        el.classList.add('bg-green-500', 'text-white');
                        el.innerHTML = '✓';
                    } else if (s === step) {
                        el.classList.add('bg-indigo-600', 'text-white');
                        el.innerHTML = s;
                    } else {
                        el.classList.add('bg-gray-200', 'text-gray-500');
                        el.innerHTML = s;
                    }
                });

                document.querySelectorAll('.step-label').forEach(el => {
                    const s = parseInt(el.dataset.step);
                    el.className = el.className.replace(/text-indigo-\S+|text-gray-\S+/g, '').trim();
                    el.classList.add(s === step ? 'text-indigo-600' : 'text-gray-400');
                });

                currentStep = step;
            };

            // Plan selection
            document.querySelectorAll('.plan-select-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const card = btn.closest('.plan-card');
                    selectedPlanId = card.dataset.planId;
                    selectedPlanMonthly = parseFloat(card.dataset.priceMonthly);
                    selectedPlanYearly = parseFloat(card.dataset.priceYearly);
                    selectedPlanIsFree = card.dataset.isFree === '1';
                    selectedPlanName = card.querySelector('h3').textContent.trim();

                    document.getElementById('plan_id').value = selectedPlanId;
                    document.getElementById('plan_slug').value = card.dataset.planSlug;

                    // Highlight selected
                    document.querySelectorAll('.plan-card').forEach(c => c.classList.remove(
                        'ring-2', 'ring-indigo-400'));
                    card.classList.add('ring-2', 'ring-indigo-400');

                    show(2);
                });
            });

            // Billing toggle
            const toggle = document.getElementById('billing-toggle');
            toggle.addEventListener('click', () => {
                isYearly = !isYearly;
                toggle.setAttribute('aria-checked', isYearly ? 'true' : 'false');
                toggle.classList.toggle('bg-indigo-600', isYearly);
                toggle.classList.toggle('bg-gray-200', !isYearly);
                toggle.querySelector('span').classList.toggle('translate-x-6', isYearly);
                toggle.querySelector('span').classList.toggle('translate-x-1', !isYearly);
                document.getElementById('billing_cycle').value = isYearly ? 'yearly' : 'monthly';

                // Update plan prices
                document.querySelectorAll('.plan-card').forEach(card => {
                    const monthly = parseFloat(card.dataset.priceMonthly);
                    const yearly = parseFloat(card.dataset.priceYearly);
                    const isFree = card.dataset.isFree === '1';
                    const priceEl = card.querySelector('.plan-price');
                    if (isFree) return;
                    const price = isYearly ? (yearly / 12) : monthly;
                    priceEl.textContent = '$' + Math.round(price);
                });
            });

            // Step 2 → 3
            document.getElementById('go-to-3').addEventListener('click', () => {
                const companyName = document.getElementById('company_name').value.trim();
                if (!companyName) {
                    alert('Please enter your company name.');
                    return;
                }

                if (!document.getElementById('slug').value.trim()) {
                    document.getElementById('slug').value = companyName.toLowerCase().replace(/[^a-z0-9]+/g,
                        '-').replace(/^-|-$/g, '');
                }

                // Update submit label
                const price = isYearly ? selectedPlanYearly : selectedPlanMonthly;
                document.getElementById('summary-plan').textContent = selectedPlanName || 'Plan';
                document.getElementById('summary-price').textContent = selectedPlanIsFree ? 'Free' : ('$' +
                    price.toFixed(2) + (isYearly ? '/yr' : '/mo'));
                document.getElementById('summary-cycle').textContent = selectedPlanIsFree ? '' : (isYearly ?
                    'Billed annually' : 'Billed monthly');
                document.getElementById('submit-label').textContent = selectedPlanIsFree ?
                    'Create account' : 'Continue to payment';

                show(3);
            });

            // Auto-slug from company name
            document.getElementById('company_name').addEventListener('input', (e) => {
                const slug = e.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
                document.getElementById('slug').value = slug;
            });

            // Back buttons
            document.getElementById('back-to-1').addEventListener('click', () => show(1));
            document.getElementById('back-to-2').addEventListener('click', () => show(2));

            // Loading overlay on form submit
            document.getElementById('registration-form').addEventListener('submit', () => {
                const overlay = document.getElementById('creating-overlay');
                overlay.classList.remove('hidden');
                document.getElementById('submit-label').textContent = 'Please wait…';
            });

            // Restore plan_slug from old input after validation errors
            @if (old('plan_slug'))
                document.getElementById('plan_slug').value = '{{ old('plan_slug') }}';
                document.getElementById('plan_id').value = '{{ old('plan_id') }}';
            @endif

            // If there are validation errors, restore appropriate step
            @if ($errors->has('company_name') || $errors->has('slug'))
                show(2);
            @elseif ($errors->has('name') || $errors->has('email') || $errors->has('password'))
                show(3);
            @elseif ($errors->has('plan_slug') || $errors->has('billing_cycle'))
                show(1);
            @endif
        });
    </script>
</body>

</html>
