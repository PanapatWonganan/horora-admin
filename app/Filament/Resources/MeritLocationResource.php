<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeritLocationResource\Pages;
use App\Filament\Resources\MeritLocationResource\RelationManagers;
use App\Models\MeritLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MeritLocationResource extends Resource
{
    protected static ?string $model = MeritLocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'สถานที่ทำบุญ';
    protected static ?string $modelLabel = 'สถานที่';
    protected static ?string $pluralModelLabel = 'สถานที่ทำบุญ';
    protected static ?string $navigationGroup = 'ทำบุญออนไลน์';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ข้อมูลสถานที่')
                    ->schema([
                        Forms\Components\TextInput::make('name_th')
                            ->label('ชื่อภาษาไทย')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_en')
                            ->label('ชื่อภาษาอังกฤษ')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('รายละเอียด')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('belief')
                            ->label('ความเชื่อ/ขอพรเรื่อง')
                            ->maxLength(255)
                            ->helperText('เช่น: ขอโชคลาภ, ขอคู่ครอง, ขอสุขภาพ'),
                        Forms\Components\TextInput::make('address')
                            ->label('ที่อยู่')
                            ->maxLength(500),
                    ])->columns(2),

                Forms\Components\Section::make('รูปภาพและการตั้งค่า')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url')
                            ->label('รูปสถานที่')
                            ->image()
                            ->directory('locations')
                            ->imageEditor(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('เปิดใช้งาน')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('ลำดับการแสดง')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('รูป')
                    ->circular(),
                Tables\Columns\TextColumn::make('name_th')
                    ->label('ชื่อสถานที่')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('belief')
                    ->label('ความเชื่อ')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('เปิดใช้งาน')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ลำดับ')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('จำนวนออเดอร์')
                    ->counts('orders')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('สถานะ')
                    ->placeholder('ทั้งหมด')
                    ->trueLabel('เปิดใช้งาน')
                    ->falseLabel('ปิดใช้งาน'),
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
            'index' => Pages\ListMeritLocations::route('/'),
            'create' => Pages\CreateMeritLocation::route('/create'),
            'edit' => Pages\EditMeritLocation::route('/{record}/edit'),
        ];
    }
}
