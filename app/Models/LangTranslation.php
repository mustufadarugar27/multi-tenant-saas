<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

final class LangTranslation extends Model
{
    use CentralConnection;
    protected $fillable = [
        'group',
        'key',
        'label',
        'meta',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'meta'      => 'array',
            'is_active' => 'boolean',
        ];
    }
}
