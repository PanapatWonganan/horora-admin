<?php

namespace App\Filament\Resources\MeritOrderResource\Pages;

use App\Filament\Resources\MeritOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeritOrder extends EditRecord
{
    protected static string $resource = MeritOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
