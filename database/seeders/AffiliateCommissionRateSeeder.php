<?php

namespace Database\Seeders;

use App\Models\AffiliateCommissionRate;
use Illuminate\Database\Seeder;

class AffiliateCommissionRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            // Basic package (฿199)
            ['package_id' => 'basic', 'tier' => 'bronze', 'rate' => 15.00],
            ['package_id' => 'basic', 'tier' => 'silver', 'rate' => 17.00],
            ['package_id' => 'basic', 'tier' => 'gold', 'rate' => 20.00],
            ['package_id' => 'basic', 'tier' => 'platinum', 'rate' => 23.00],

            // Standard package (฿399)
            ['package_id' => 'standard', 'tier' => 'bronze', 'rate' => 15.00],
            ['package_id' => 'standard', 'tier' => 'silver', 'rate' => 17.00],
            ['package_id' => 'standard', 'tier' => 'gold', 'rate' => 20.00],
            ['package_id' => 'standard', 'tier' => 'platinum', 'rate' => 23.00],

            // Premium package (฿699)
            ['package_id' => 'premium', 'tier' => 'bronze', 'rate' => 12.00],
            ['package_id' => 'premium', 'tier' => 'silver', 'rate' => 14.00],
            ['package_id' => 'premium', 'tier' => 'gold', 'rate' => 17.00],
            ['package_id' => 'premium', 'tier' => 'platinum', 'rate' => 20.00],

            // VIP package (฿1,299)
            ['package_id' => 'vip', 'tier' => 'bronze', 'rate' => 10.00],
            ['package_id' => 'vip', 'tier' => 'silver', 'rate' => 12.00],
            ['package_id' => 'vip', 'tier' => 'gold', 'rate' => 15.00],
            ['package_id' => 'vip', 'tier' => 'platinum', 'rate' => 18.00],
        ];

        foreach ($rates as $rate) {
            AffiliateCommissionRate::updateOrCreate(
                ['package_id' => $rate['package_id'], 'tier' => $rate['tier']],
                $rate
            );
        }
    }
}
