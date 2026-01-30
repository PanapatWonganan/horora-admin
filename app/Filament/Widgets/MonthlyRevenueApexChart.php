<?php

namespace App\Filament\Widgets;

use App\Models\MeritOrder;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MonthlyRevenueApexChart extends ApexChartWidget
{
    protected static ?string $chartId = 'monthlyRevenueApexChart';
    protected static ?string $heading = 'รายได้ย้อนหลัง 6 เดือน';
    protected static ?int $sort = 4;
    protected static ?string $pollingInterval = '60s';

    protected function getOptions(): array
    {
        $data = collect();
        $labels = collect();

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels->push($month->locale('th')->isoFormat('MMM YYYY'));

            $monthlySales = MeritOrder::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->whereIn('status', ['paid', 'processing', 'completed'])
                ->sum('price');

            $data->push($monthlySales);
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 280,
                'toolbar' => [
                    'show' => false,
                ],
                'animations' => [
                    'enabled' => true,
                    'easing' => 'easeinout',
                    'speed' => 800,
                    'animateGradually' => [
                        'enabled' => true,
                        'delay' => 150,
                    ],
                    'dynamicAnimation' => [
                        'enabled' => true,
                        'speed' => 350,
                    ],
                ],
            ],
            'series' => [
                [
                    'name' => 'รายได้',
                    'data' => $data->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $labels->toArray(),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontWeight' => 500,
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'formatter' => 'function (val) { return "฿" + val.toLocaleString(); }',
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'formatter' => 'function (val) { return "฿" + (val/1000).toFixed(0) + "K"; }',
                'offsetY' => -20,
                'style' => [
                    'fontSize' => '12px',
                    'colors' => ['#6366F1'],
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 8,
                    'borderRadiusApplication' => 'end',
                    'columnWidth' => '60%',
                    'dataLabels' => [
                        'position' => 'top',
                    ],
                    'distributed' => true,
                ],
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'light',
                    'type' => 'vertical',
                    'shadeIntensity' => 0.25,
                    'gradientToColors' => ['#EC4899'],
                    'opacityFrom' => 1,
                    'opacityTo' => 0.85,
                    'stops' => [0, 100],
                ],
            ],
            'colors' => ['#8B5CF6', '#A855F7', '#C084FC', '#D8B4FE', '#D946EF', '#EC4899'],
            'legend' => [
                'show' => false,
            ],
            'grid' => [
                'borderColor' => '#e5e7eb',
                'strokeDashArray' => 4,
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function (val) { return "฿" + val.toLocaleString(); }',
                ],
            ],
        ];
    }
}
