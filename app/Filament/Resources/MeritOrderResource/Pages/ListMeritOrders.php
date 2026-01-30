<?php

namespace App\Filament\Resources\MeritOrderResource\Pages;

use App\Filament\Resources\MeritOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMeritOrders extends ListRecords
{
    protected static string $resource = MeritOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
