<?php

namespace App\Filament\Widgets;

use App\Models\MeritOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class OrdersByLocationApexChart extends ApexChartWidget
{
    protected static ?string $chartId = 'ordersByLocationApexChart';
    protected static ?string $heading = 'ยอดขายตามสถานที่';
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = '60s';

    protected function getOptions(): array
    {
        $data = MeritOrder::select('merit_locations.name_th', DB::raw('SUM(merit_orders.price) as total'))
            ->join('merit_locations', 'merit_orders.location_id', '=', 'merit_locations.id')
            ->whereIn('merit_orders.status', ['paid', 'processing', 'completed'])
            ->groupBy('merit_locations.id', 'merit_locations.name_th')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // ตัดชื่อให้สั้นลง
        $labels = $data->pluck('name_th')->map(function ($name) {
            return Str::limit($name, 20, '...');
        })->toArray();

        // สีสวยๆ สำหรับ Donut chart
        $colors = [
            '#8B5CF6',   // Violet
            '#EC4899',   // Pink
            '#F97316',   // Orange
            '#22C55E',   // Green
            '#3B82F6',   // Blue
            '#A855F7',   // Purple
        ];

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 320,
                'animations' => [
                    'enabled' => true,
                    'easing' => 'easeinout',
                    'speed' => 800,
                ],
            ],
            'series' => $data->pluck('total')->map(fn($v) => (float) $v)->toArray(),
            'labels' => $labels,
            'colors' => array_slice($colors, 0, $data->count()),
            'dataLabels' => [
                'enabled' => true,
                'style' => [
                    'fontSize' => '12px',
                ],
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '55%',
                        'labels' => [
                            'show' => true,
                            'name' => [
                                'show' => true,
                                'fontSize' => '12px',
                            ],
                            'value' => [
                                'show' => true,
                                'fontSize' => '16px',
                                'fontWeight' => 700,
                            ],
                            'total' => [
                                'show' => true,
                                'label' => 'รวม',
                                'fontSize' => '12px',
                            ],
                        ],
                    ],
                    'expandOnClick' => true,
                ],
            ],
            'legend' => [
                'position' => 'bottom',
                'horizontalAlign' => 'center',
                'fontSize' => '11px',
                'markers' => [
                    'width' => 10,
                    'height' => 10,
                ],
                'itemMargin' => [
                    'horizontal' => 8,
                    'vertical' => 3,
                ],
            ],
            'stroke' => [
                'width' => 2,
                'colors' => ['#1f2937'],
            ],
            'tooltip' => [
                'enabled' => true,
            ],
        ];
    }
}
