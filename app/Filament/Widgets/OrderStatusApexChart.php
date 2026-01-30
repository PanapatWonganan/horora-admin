<?php

namespace App\Filament\Widgets;

use App\Models\MeritOrder;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class OrderStatusApexChart extends ApexChartWidget
{
    protected static ?string $chartId = 'orderStatusApexChart';
    protected static ?string $heading = 'สถานะคำสั่งซื้อ';
    protected static ?int $sort = 5;
    protected static ?string $pollingInterval = '30s';

    protected function getOptions(): array
    {
        $statuses = [
            'pending' => ['label' => 'รอชำระเงิน', 'color' => '#FBBF24'],
            'paid' => ['label' => 'รอดำเนินการ', 'color' => '#3B82F6'],
            'processing' => ['label' => 'กำลังไหว้', 'color' => '#8B5CF6'],
            'completed' => ['label' => 'เสร็จสิ้น', 'color' => '#22C55E'],
            'cancelled' => ['label' => 'ยกเลิก', 'color' => '#EF4444'],
        ];

        $data = [];
        $labels = [];
        $colors = [];

        foreach ($statuses as $status => $info) {
            $count = MeritOrder::where('status', $status)->count();
            if ($count > 0) {
                $data[] = $count;
                $labels[] = $info['label'];
                $colors[] = $info['color'];
            }
        }

        return [
            'chart' => [
                'type' => 'radialBar',
                'height' => 280,
                'animations' => [
                    'enabled' => true,
                    'easing' => 'easeinout',
                    'speed' => 800,
                ],
            ],
            'series' => array_map(function ($val) use ($data) {
                $total = array_sum($data);
                return $total > 0 ? round(($val / $total) * 100) : 0;
            }, $data),
            'labels' => $labels,
            'colors' => $colors,
            'plotOptions' => [
                'radialBar' => [
                    'hollow' => [
                        'size' => '40%',
                    ],
                    'track' => [
                        'background' => '#f1f5f9',
                        'strokeWidth' => '100%',
                    ],
                    'dataLabels' => [
                        'name' => [
                            'fontSize' => '14px',
                            'fontWeight' => 600,
                        ],
                        'value' => [
                            'fontSize' => '16px',
                            'fontWeight' => 700,
                            'formatter' => 'function (val) { return val + "%"; }',
                        ],
                        'total' => [
                            'show' => true,
                            'label' => 'ทั้งหมด',
                            'fontSize' => '14px',
                            'formatter' => 'function (w) { return w.globals.seriesTotals.length + " สถานะ"; }',
                        ],
                    ],
                ],
            ],
            'legend' => [
                'show' => true,
                'position' => 'bottom',
                'horizontalAlign' => 'center',
                'fontSize' => '13px',
            ],
            'stroke' => [
                'lineCap' => 'round',
            ],
        ];
    }
}
