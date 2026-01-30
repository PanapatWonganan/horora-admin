<?php

namespace App\Filament\Widgets;

use App\Models\MeritOrder;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // ยอดขายวันนี้
        $todaySales = MeritOrder::whereDate('created_at', $today)
            ->whereIn('status', ['paid', 'processing', 'completed'])
            ->sum('price');

        // ยอดขายเดือนนี้
        $thisMonthSales = MeritOrder::where('created_at', '>=', $thisMonth)
            ->whereIn('status', ['paid', 'processing', 'completed'])
            ->sum('price');

        // ยอดขายเดือนที่แล้ว (สำหรับเปรียบเทียบ)
        $lastMonthSales = MeritOrder::whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->whereIn('status', ['paid', 'processing', 'completed'])
            ->sum('price');

        // คำนวณ % เปลี่ยนแปลง
        $salesChange = $lastMonthSales > 0
            ? round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1)
            : 0;

        // ออเดอร์รอดำเนินการ
        $pendingOrders = MeritOrder::where('status', 'paid')->count();

        // ออเดอร์ทั้งหมดเดือนนี้
        $thisMonthOrders = MeritOrder::where('created_at', '>=', $thisMonth)->count();

        // ยอดรวมทั้งหมด
        $totalRevenue = MeritOrder::whereIn('status', ['paid', 'processing', 'completed'])
            ->sum('price');

        // ข้อมูล 7 วันสำหรับ chart
        $last7DaysSales = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dailySales = MeritOrder::whereDate('created_at', $date)
                ->whereIn('status', ['paid', 'processing', 'completed'])
                ->sum('price');
            $last7DaysSales->push($dailySales);
        }

        // ผู้ใช้ใหม่เดือนนี้
        $newUsers = User::where('created_at', '>=', $thisMonth)->count();

        return [
            Stat::make('ยอดขายวันนี้', '฿' . number_format($todaySales, 0))
                ->description('รายได้วันนี้')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart($last7DaysSales->toArray()),

            Stat::make('ยอดขายเดือนนี้', '฿' . number_format($thisMonthSales, 0))
                ->description($salesChange >= 0 ? "+{$salesChange}% จากเดือนที่แล้ว" : "{$salesChange}% จากเดือนที่แล้ว")
                ->descriptionIcon($salesChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesChange >= 0 ? 'success' : 'danger')
                ->chart($last7DaysSales->toArray()),

            Stat::make('รอดำเนินการ', $pendingOrders . ' ออเดอร์')
                ->description('ต้องดำเนินการด่วน!')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),

            Stat::make('ออเดอร์เดือนนี้', number_format($thisMonthOrders))
                ->description('คำสั่งซื้อทั้งหมด')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('ยอดรวมทั้งหมด', '฿' . number_format($totalRevenue, 0))
                ->description('รายได้สะสม')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('ผู้ใช้ใหม่', number_format($newUsers))
                ->description('สมัครเดือนนี้')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('gray'),
        ];
    }
}
