@php
$color = \App\Support\LangTranslations::attr('task_priority', $priority, 'badge_class', 'bg-gray-100 text-gray-600 ring-gray-200');
$dot   = \App\Support\LangTranslations::attr('task_priority', $priority, 'dot_class', 'bg-gray-400');
$label = \App\Support\LangTranslations::attr('task_priority', $priority, 'label', ucfirst($priority));
@endphp
<span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $color }}">
    <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>
    {{ $label }}
</span>
