<?php

namespace App\Filament\Resources\MeritPackageResource\Pages;

use App\Filament\Resources\MeritPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeritPackage extends EditRecord
{
    protected static string $resource = MeritPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
