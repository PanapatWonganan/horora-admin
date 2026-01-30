<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeritOrderResource\Pages;
use App\Models\MeritOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MeritOrderResource extends Resource
{
    protected static ?string $model = MeritOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'คำสั่งซื้อทำบุญ';
    protected static ?string $modelLabel = 'คำสั่งซื้อ';
    protected static ?string $pluralModelLabel = 'คำสั่งซื้อทำบุญ';
    protected static ?string $navigationGroup = 'ทำบุญออนไลน์';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ข้อมูลคำสั่งซื้อ')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('เลขที่คำสั่งซื้อ')
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->label('ผู้ใช้')
                            ->relationship('user', 'name')
                            ->searchable(),
                        Forms\Components\Select::make('location_id')
                            ->label('สถานที่')
                            ->relationship('location', 'name_th')
                            ->required(),
                        Forms\Components\Select::make('package_id')
                            ->label('แพ็คเกจ')
                            ->relationship('package', 'name_th')
                            ->required(),
                        Forms\Components\TextInput::make('price')
                            ->label('ราคา')
                            ->required()
                            ->numeric()
                            ->prefix('฿'),
                        Forms\Components\Select::make('status')
                            ->label('สถานะ')
                            ->options([
                                'pending' => 'รอชำระเงิน',
                                'paid' => 'รอดำเนินการ',
                                'processing' => 'กำลังไหว้',
                                'completed' => 'เสร็จสิ้น',
                                'cancelled' => 'ยกเลิก',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('ข้อมูลผู้ขอพร')
                    ->schema([
                        Forms\Components\TextInput::make('prayer_name')
                            ->label('ชื่อผู้ขอพร')
                            ->required(),
                        Forms\Components\DatePicker::make('prayer_birthdate')
                            ->label('วันเกิด'),
                        Forms\Components\TextInput::make('prayer_phone')
                            ->label('เบอร์โทร')
                            ->tel(),
                        Forms\Components\Textarea::make('prayer_wish')
                            ->label('คำอธิษฐาน')
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('การชำระเงิน')
                    ->schema([
                        Forms\Components\FileUpload::make('slip_url')
                            ->label('สลิปการโอน')
                            ->image()
                            ->directory('slips'),
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('ชำระเงินเมื่อ'),
                    ])->columns(2),

                Forms\Components\Section::make('หลักฐานการไหว้')
                    ->schema([
                        Forms\Components\FileUpload::make('proof_urls')
                            ->label('รูปหลักฐาน')
                            ->multiple()
                            ->image()
                            ->directory('proofs'),
                        Forms\Components\TextInput::make('proof_video_url')
                            ->label('ลิงก์วิดีโอ')
                            ->url(),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('เสร็จสิ้นเมื่อ'),
                        Forms\Components\Textarea::make('admin_note')
                            ->label('หมายเหตุ (Admin)')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('เลขที่')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prayer_name')
                    ->label('ผู้ขอพร')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location.name_th')
                    ->label('สถานที่')
                    ->sortable(),
                Tables\Columns\TextColumn::make('package.name_th')
                    ->label('แพ็คเกจ'),
                Tables\Columns\TextColumn::make('price')
                    ->label('ราคา')
                    ->money('THB')
                    ->sortable(),
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
                        'pending' => 'รอชำระเงิน',
                        'paid' => 'รอดำเนินการ',
                        'processing' => 'กำลังไหว้',
                        'completed' => 'เสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('วันที่สั่ง')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('order_number', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'pending' => 'รอชำระเงิน',
                        'paid' => 'รอดำเนินการ',
                        'processing' => 'กำลังไหว้',
                        'completed' => 'เสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                    ]),
                Tables\Filters\SelectFilter::make('location_id')
                    ->label('สถานที่')
                    ->relationship('location', 'name_th'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeritOrders::route('/'),
            'create' => Pages\CreateMeritOrder::route('/create'),
            'edit' => Pages\EditMeritOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'paid')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
