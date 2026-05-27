<?php

namespace App\Services;

use App\Models\Plan;

class PlanCatalog
{
    /**
     * @return array<string, array{name:string, price_paise:int, downloads_allowed:?int, duration_days:int}>
     */
    public static function defaults(): array
    {
        return [
            'basic' => [
                'name' => 'Basic',
                'price_paise' => 29900,
                'downloads_allowed' => 1,
                'duration_days' => 14,
            ],
            'silver' => [
                'name' => 'Silver',
                'price_paise' => 59900,
                'downloads_allowed' => 3,
                'duration_days' => 45,
            ],
            'gold' => [
                'name' => 'Gold',
                'price_paise' => 149900,
                'downloads_allowed' => null,
                'duration_days' => 365,
            ],
        ];
    }

    public function ensurePlan(string $slug): Plan
    {
        $config = self::defaults()[$slug] ?? null;

        abort_if(! $config, 404, 'Invalid plan.');

        return Plan::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $config['name'],
                'price_paise' => $config['price_paise'],
                'downloads_allowed' => $config['downloads_allowed'],
                'duration_days' => $config['duration_days'],
                'resume_limit' => $config['downloads_allowed'],
                'cover_letter_limit' => null,
                'ai_enabled' => $slug !== 'basic',
                'is_active' => true,
            ]
        );
    }
}
