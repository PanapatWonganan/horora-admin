<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateWithdrawalResource\Pages;
use App\Models\AffiliateWithdrawal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliateWithdrawalResource extends Resource
{
    protected static ?string $model = AffiliateWithdrawal::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Affiliate';
    protected static ?string $navigationLabel = 'คำขอถอนเงิน';
    protected static ?string $modelLabel = 'คำขอถอนเงิน';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ข้อมูลการถอน')->schema([
                Forms\Components\Select::make('affiliate_id')
                    ->label('ตัวแทน')
                    ->relationship('affiliate', 'referral_code')
                    ->disabled(),
                Forms\Components\TextInput::make('amount')
                    ->label('จำนวนเงิน')
                    ->disabled(),
                Forms\Components\Select::make('method')
                    ->label('วิธีรับเงิน')
                    ->options([
                        'bank_transfer' => 'โอนธนาคาร',
                        'promptpay' => 'พร้อมเพย์',
                    ])
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->label('สถานะ')
                    ->options([
                        'pending' => 'รอดำเนินการ',
                        'processing' => 'กำลังโอน',
                        'completed' => 'โอนแล้ว',
                        'rejected' => 'ปฏิเสธ',
                    ]),
            ])->columns(2),

            Forms\Components\Section::make('ข้อมูลบัญชี')->schema([
                Forms\Components\TextInput::make('bank_name')->label('ธนาคาร')->disabled(),
                Forms\Components\TextInput::make('bank_account_number')->label('เลขบัญชี')->disabled(),
                Forms\Components\TextInput::make('bank_account_name')->label('ชื่อบัญชี')->disabled(),
                Forms\Components\TextInput::make('promptpay_number')->label('พร้อมเพย์')->disabled(),
            ])->columns(2),

            Forms\Components\Section::make('การดำเนินการ')->schema([
                Forms\Components\FileUpload::make('transfer_slip_url')
                    ->label('สลิปโอนเงิน')
                    ->image()
                    ->directory('withdrawal-slips'),
                Forms\Components\Textarea::make('admin_note')
                    ->label('หมายเหตุ Admin'),
                Forms\Components\DateTimePicker::make('processed_at')
                    ->label('วันที่ดำเนินการ'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('affiliate.referral_code')
                    ->label('รหัสตัวแทน')
                    ->searchable(),
                Tables\Columns\TextColumn::make('affiliate.user.name')
                    ->label('ชื่อ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('จำนวนเงิน')
                    ->money('THB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->label('วิธี')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'promptpay' => 'พร้อมเพย์',
                        default => 'โอนธนาคาร',
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('สถานะ')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'completed',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'processing' => 'กำลังโอน',
                        'completed' => 'โอนแล้ว',
                        'rejected' => 'ปฏิเสธ',
                        default => 'รอดำเนินการ',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('วันที่ขอ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'รอดำเนินการ',
                        'processing' => 'กำลังโอน',
                        'completed' => 'โอนแล้ว',
                        'rejected' => 'ปฏิเสธ',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('อนุมัติ')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (AffiliateWithdrawal $record): bool => $record->status === 'pending')
                    ->action(function (AffiliateWithdrawal $record) {
                        $record->update([
                            'status' => 'processing',
                            'processed_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('complete')
                    ->label('โอนแล้ว')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (AffiliateWithdrawal $record): bool => $record->status === 'processing')
                    ->action(function (AffiliateWithdrawal $record) {
                        $record->update([
                            'status' => 'completed',
                            'processed_at' => now(),
                        ]);
                        $record->affiliate->recalculateBalance();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('ปฏิเสธ')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (AffiliateWithdrawal $record): bool => in_array($record->status, ['pending', 'processing']))
                    ->requiresConfirmation()
                    ->action(function (AffiliateWithdrawal $record) {
                        $record->update([
                            'status' => 'rejected',
                            'processed_at' => now(),
                        ]);
                        $record->affiliate->recalculateBalance();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliateWithdrawals::route('/'),
            'edit' => Pages\EditAffiliateWithdrawal::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }
}
