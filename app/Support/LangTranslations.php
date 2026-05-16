<?php


namespace App\Support;

use App\Models\LangTranslation;
use Illuminate\Support\Facades\Cache;

final class LangTranslations
{
    private const CACHE_KEY = 'lang_translations';

    public static function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => self::loadFromDb());
    }

    public static function group(string $group): array
    {
        return self::all()[$group] ?? [];
    }

    public static function get(string $group, string $key): array
    {
        return self::all()[$group][$key] ?? [];
    }

    public static function attr(string $group, string $key, string $attribute, mixed $default = null): mixed
    {
        return self::all()[$group][$key][$attribute] ?? $default;
    }

    public static function keys(string $group): array
    {
        return array_keys(self::group($group));
    }

    /** @return EnumOption[] */
    public static function options(string $group): array
    {
        return collect(self::group($group))
            ->map(fn (array $data, string $key) => new EnumOption(
                $key,
                $data['label'] ?? ucfirst(str_replace('_', ' ', $key)),
            ))
            ->values()
            ->all();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private static function loadFromDb(): array
    {
        return LangTranslation::query()
            ->where('is_active', true)
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group')
            ->map(fn ($rows) => $rows->mapWithKeys(fn (LangTranslation $row) => [
                $row->key => array_merge(['label' => $row->label], $row->meta ?? []),
            ])->all())
            ->all();
    }
}
