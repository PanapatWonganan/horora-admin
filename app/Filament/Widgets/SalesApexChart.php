<?php

namespace App\Filament\Widgets;

use App\Models\MeritOrder;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class SalesApexChart extends ApexChartWidget
{
    protected static ?string $chartId = 'salesApexChart';
    protected static ?string $heading = 'ยอดขาย 7 วันล่าสุด';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';

    protected function getOptions(): array
    {
        $salesData = collect();
        $ordersData = collect();
        $labels = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels->push($date->format('d/m'));

            $dailySales = MeritOrder::whereDate('created_at', $date)
                ->whereIn('status', ['paid', 'processing', 'completed'])
                ->sum('price');

            $dailyOrders = MeritOrder::whereDate('created_at', $date)->count();

            $salesData->push((float) $dailySales);
            $ordersData->push((int) $dailyOrders);
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 350,
                'toolbar' => [
                    'show' => false,
                ],
                'animations' => [
                    'enabled' => true,
                    'easing' => 'easeinout',
                    'speed' => 800,
                ],
            ],
            'series' => [
                [
                    'name' => 'ยอดขาย (บาท)',
                    'data' => $salesData->toArray(),
                ],
                [
                    'name' => 'จำนวนออเดอร์',
                    'data' => $ordersData->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $labels->toArray(),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 3,
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'light',
                    'type' => 'vertical',
                    'shadeIntensity' => 0.5,
                    'opacityFrom' => 0.7,
                    'opacityTo' => 0.2,
                ],
            ],
            'colors' => ['#8B5CF6', '#F97316'],
            'legend' => [
                'position' => 'bottom',
                'horizontalAlign' => 'center',
                'offsetY' => 5,
            ],
            'grid' => [
                'borderColor' => '#e5e7eb',
                'strokeDashArray' => 4,
            ],
            'tooltip' => [
                'shared' => true,
                'intersect' => false,
            ],
            'markers' => [
                'size' => 5,
                'hover' => [
                    'size' => 8,
                ],
            ],
        ];
    }
}
