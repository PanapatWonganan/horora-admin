<?php

namespace App\Filament\Resources\MeritLocationResource\Pages;

use App\Filament\Resources\MeritLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeritLocation extends EditRecord
{
    protected static string $resource = MeritLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
