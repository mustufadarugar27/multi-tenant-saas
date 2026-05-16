<?php


namespace Database\Seeders;

use App\Models\LangTranslation;
use App\Support\LangTranslations;
use Illuminate\Database\Seeder;

final class LangTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $groups = config('enums');

        $sortOrder = 0;

        foreach ($groups as $group => $entries) {
            $sortOrder = 0;

            foreach ($entries as $key => $data) {
                $label = $data['label'] ?? ucfirst(str_replace('_', ' ', $key));
                $meta  = array_diff_key($data, ['label' => null]);

                LangTranslation::updateOrCreate(
                    ['group' => $group, 'key' => $key],
                    [
                        'label'      => $label,
                        'meta'       => empty($meta) ? null : $meta,
                        'sort_order' => $sortOrder++,
                        'is_active'  => true,
                    ],
                );
            }
        }

        LangTranslations::flush();
    }
}
