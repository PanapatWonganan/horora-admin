<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateResource\Pages;
use App\Models\Affiliate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliateResource extends Resource
{
    protected static ?string $model = Affiliate::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Affiliate';
    protected static ?string $navigationLabel = 'ตัวแทน';
    protected static ?string $modelLabel = 'ตัวแทน';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ข้อมูลตัวแทน')->schema([
                Forms\Components\Select::make('user_id')
                    ->label('ผู้ใช้')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('referral_code')
                    ->label('รหัสแนะนำ')
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\Select::make('tier')
                    ->label('ระดับ')
                    ->options([
                        'bronze' => 'Bronze',
                        'silver' => 'Silver',
                        'gold' => 'Gold',
                        'platinum' => 'Platinum',
                    ]),
                Forms\Components\Select::make('status')
                    ->label('สถานะ')
                    ->options([
                        'pending' => 'รอตรวจสอบ',
                        'active' => 'ใช้งาน',
                        'suspended' => 'ระงับ',
                    ]),
            ])->columns(2),

            Forms\Components\Section::make('ข้อมูลธนาคาร')->schema([
                Forms\Components\TextInput::make('bank_name')->label('ธนาคาร'),
                Forms\Components\TextInput::make('bank_account_number')->label('เลขบัญชี'),
                Forms\Components\TextInput::make('bank_account_name')->label('ชื่อบัญชี'),
                Forms\Components\TextInput::make('promptpay_number')->label('พร้อมเพย์'),
            ])->columns(2),

            Forms\Components\Section::make('สถิติ')->schema([
                Forms\Components\TextInput::make('total_referrals')->label('แนะนำทั้งหมด')->disabled(),
                Forms\Components\TextInput::make('total_orders')->label('ออเดอร์ทั้งหมด')->disabled(),
                Forms\Components\TextInput::make('total_commission_earned')->label('คอมมิชชั่นรวม')->disabled(),
                Forms\Components\TextInput::make('total_commission_paid')->label('จ่ายแล้ว')->disabled(),
                Forms\Components\TextInput::make('available_balance')->label('ยอดคงเหลือ')->disabled(),
            ])->columns(3),

            Forms\Components\Textarea::make('note')->label('หมายเหตุ')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('referral_code')
                    ->label('รหัส')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('ชื่อ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('อีเมล')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('tier')
                    ->label('ระดับ')
                    ->colors([
                        'gray' => 'bronze',
                        'warning' => 'silver',
                        'success' => 'gold',
                        'primary' => 'platinum',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('สถานะ')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'danger' => 'suspended',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'ใช้งาน',
                        'suspended' => 'ระงับ',
                        default => 'รอตรวจสอบ',
                    }),
                Tables\Columns\TextColumn::make('total_orders')
                    ->label('ออเดอร์')
                    ->sortable(),
                Tables\Columns\TextColumn::make('available_balance')
                    ->label('ยอดคงเหลือ')
                    ->money('THB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_commission_earned')
                    ->label('คอมมิชชั่นรวม')
                    ->money('THB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สมัครเมื่อ')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'รอตรวจสอบ',
                        'active' => 'ใช้งาน',
                        'suspended' => 'ระงับ',
                    ]),
                Tables\Filters\SelectFilter::make('tier')
                    ->options([
                        'bronze' => 'Bronze',
                        'silver' => 'Silver',
                        'gold' => 'Gold',
                        'platinum' => 'Platinum',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('activate')
                    ->label('อนุมัติ')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Affiliate $record): bool => $record->status === 'pending')
                    ->action(fn (Affiliate $record) => $record->update(['status' => 'active'])),
                Tables\Actions\Action::make('suspend')
                    ->label('ระงับ')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Affiliate $record): bool => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(fn (Affiliate $record) => $record->update(['status' => 'suspended'])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliates::route('/'),
            'create' => Pages\CreateAffiliate::route('/create'),
            'edit' => Pages\EditAffiliate::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }
}
