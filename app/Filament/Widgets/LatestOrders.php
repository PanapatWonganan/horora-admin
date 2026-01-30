<?php

namespace App\Filament\Widgets;

use App\Models\MeritOrder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?string $heading = 'ออเดอร์ล่าสุด';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MeritOrder::query()
                    ->with(['location', 'package'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('เลขที่')
                    ->searchable(),
                Tables\Columns\TextColumn::make('prayer_name')
                    ->label('ผู้ขอพร')
                    ->limit(20),
                Tables\Columns\TextColumn::make('location.name_th')
                    ->label('สถานที่')
                    ->limit(15),
                Tables\Columns\TextColumn::make('package.name_th')
                    ->label('แพ็คเกจ'),
                Tables\Columns\TextColumn::make('price')
                    ->label('ราคา')
                    ->money('THB'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('สถานะ')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'paid',
                        'primary' => 'processing',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'รอชำระ',
                        'paid' => 'รอดำเนินการ',
                        'processing' => 'กำลังไหว้',
                        'completed' => 'เสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('วันที่')
                    ->dateTime('d/m/y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('ดู')
                    ->url(fn (MeritOrder $record): string => route('filament.admin.resources.merit-orders.edit', $record))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
