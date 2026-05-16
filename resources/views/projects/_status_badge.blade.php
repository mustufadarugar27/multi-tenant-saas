@php
$color = \App\Support\LangTranslations::attr('project_status', $status, 'badge_class', 'bg-gray-100 text-gray-700 ring-gray-300');
$label = \App\Support\LangTranslations::attr('project_status', $status, 'label', ucfirst(str_replace('_', ' ', $status)));
@endphp
<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $color }}">
    {{ $label }}
</span>
