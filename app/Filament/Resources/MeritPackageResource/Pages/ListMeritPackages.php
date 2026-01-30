<?php

namespace App\Filament\Resources\MeritPackageResource\Pages;

use App\Filament\Resources\MeritPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMeritPackages extends ListRecords
{
    protected static string $resource = MeritPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
