<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeritPackageResource\Pages;
use App\Filament\Resources\MeritPackageResource\RelationManagers;
use App\Models\MeritPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MeritPackageResource extends Resource
{
    protected static ?string $model = MeritPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationLabel = 'แพ็คเกจทำบุญ';
    protected static ?string $modelLabel = 'แพ็คเกจ';
    protected static ?string $pluralModelLabel = 'แพ็คเกจทำบุญ';
    protected static ?string $navigationGroup = 'ทำบุญออนไลน์';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ข้อมูลแพ็คเกจ')
                    ->schema([
                        Forms\Components\TextInput::make('name_th')
                            ->label('ชื่อแพ็คเกจ (ไทย)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_en')
                            ->label('ชื่อแพ็คเกจ (อังกฤษ)')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('รายละเอียด')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('items')
                            ->label('รายการที่รวม')
                            ->rows(3)
                            ->helperText('เช่น: ธูป 9 ดอก, ดอกบัว 1 ดอก, เทียนแดง 2 เล่ม')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('ราคาและการส่งมอบ')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('ราคา')
                            ->required()
                            ->numeric()
                            ->prefix('฿')
                            ->suffix('บาท'),
                        Forms\Components\TextInput::make('photo_count')
                            ->label('จำนวนรูปถ่าย')
                            ->numeric()
                            ->default(3)
                            ->helperText('จำนวนรูปหลักฐานที่จะส่งให้'),
                        Forms\Components\Toggle::make('has_video')
                            ->label('มีวิดีโอ')
                            ->helperText('ถ่ายวิดีโอขณะทำพิธี'),
                        Forms\Components\Toggle::make('has_live')
                            ->label('ถ่ายทอดสด')
                            ->helperText('Live สดขณะทำพิธี'),
                    ])->columns(4),

                Forms\Components\Section::make('การตั้งค่า')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('เปิดใช้งาน')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('ลำดับการแสดง')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_th')
                    ->label('ชื่อแพ็คเกจ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('ราคา')
                    ->money('THB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('photo_count')
                    ->label('รูปถ่าย')
                    ->numeric()
                    ->suffix(' รูป'),
                Tables\Columns\IconColumn::make('has_video')
                    ->label('วิดีโอ')
                    ->boolean(),
                Tables\Columns\IconColumn::make('has_live')
                    ->label('Live')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('เปิดใช้งาน')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ลำดับ')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('ยอดขาย')
                    ->counts('orders')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('สถานะ')
                    ->placeholder('ทั้งหมด')
                    ->trueLabel('เปิดใช้งาน')
                    ->falseLabel('ปิดใช้งาน'),
                Tables\Filters\TernaryFilter::make('has_video')
                    ->label('มีวิดีโอ'),
                Tables\Filters\TernaryFilter::make('has_live')
                    ->label('มี Live'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeritPackages::route('/'),
            'create' => Pages\CreateMeritPackage::route('/create'),
            'edit' => Pages\EditMeritPackage::route('/{record}/edit'),
        ];
    }
}
