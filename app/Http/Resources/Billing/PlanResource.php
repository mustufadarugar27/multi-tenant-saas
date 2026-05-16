<?php


namespace App\Http\Resources\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'name'                    => $this->name,
            'slug'                    => $this->slug,
            'description'             => $this->description,
            'price_monthly'           => $this->price_monthly,
            'price_yearly'            => $this->price_yearly,
            'features'                => $this->features,
            'max_users'               => $this->max_users,
            'max_projects'            => $this->max_projects,
            'storage_gb'              => $this->storage_gb,
            'is_free'                 => $this->is_free,
            'sort_order'              => $this->sort_order,
        ];
    }
}
