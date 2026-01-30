<?php

namespace App\Filament\Resources\MeritLocationResource\Pages;

use App\Filament\Resources\MeritLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMeritLocations extends ListRecords
{
    protected static string $resource = MeritLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
