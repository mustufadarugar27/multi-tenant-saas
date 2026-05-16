@extends('layouts.app')

@section('title', 'Billing — ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Billing & Subscription</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your subscription and view past invoices.</p>
    </div>

    {{-- Subscription Status Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Current Plan</h2>
                <p class="text-3xl font-bold text-indigo-600 mt-1">
                    {{ $tenant->plan?->name ?? 'No Plan' }}
                </p>
                @if($tenant->billing_cycle)
                    <p class="text-sm text-gray-500 mt-1">Billed {{ ucfirst($tenant->billing_cycle) }}</p>
                @endif
            </div>
            <div class="text-right">
                @if($tenant->subscription_status)
                    @php $color = \App\Support\LangTranslations::attr('subscription_status', $tenant->subscription_status, 'badge_color', 'gray'); @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                        {{ $color === 'green' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $color === 'red' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $color === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $color === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $color === 'orange' ? 'bg-orange-100 text-orange-800' : '' }}
                    ">
                        {{ \App\Support\LangTranslations::attr('subscription_status', $tenant->subscription_status, 'label', ucfirst($tenant->subscription_status)) }}
                    </span>
                @endif

                @if($tenant->subscription_ends_at)
                    <p class="text-sm text-gray-500 mt-2">
                        Renews {{ $tenant->subscription_ends_at->format('M j, Y') }}
                    </p>
                @endif
            </div>
        </div>

        @if($tenant->plan)
            <div class="mt-6 pt-6 border-t border-gray-100">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Plan Features</h3>
                <ul class="grid grid-cols-2 gap-2">
                    @foreach($tenant->plan->features ?? [] as $feature)
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Invoices Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Invoices</h2>
        </div>

        @if($invoices->isEmpty())
            <div class="px-6 py-16 text-center">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-400 text-sm">No invoices yet</p>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($invoices as $invoice)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-900 font-mono">
                                #{{ strtoupper(substr($invoice->id, 0, 8)) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($invoice->period_start && $invoice->period_end)
                                    {{ $invoice->period_start->format('M j') }} – {{ $invoice->period_end->format('M j, Y') }}
                                @else
                                    {{ $invoice->created_at->format('M j, Y') }}
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                {{ $invoice->formattedAmount() }}
                            </td>
                            <td class="px-6 py-4">
                                @if($invoice->isPaid())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Paid</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">{{ ucfirst($invoice->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    @if($invoice->hosted_invoice_url)
                                        <a href="{{ $invoice->hosted_invoice_url }}" target="_blank"
                                           class="text-sm text-indigo-600 hover:text-indigo-800">View</a>
                                    @endif
                                    <a href="{{ route('billing.invoices.download', $invoice) }}"
                                       class="text-sm text-gray-600 hover:text-gray-800">Download PDF</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($invoices->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $invoices->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
