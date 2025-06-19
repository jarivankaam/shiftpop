<?php

namespace App\Filament\Resources\ShiftsResource\Pages;

use App\Filament\Resources\ShiftsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShifts extends EditRecord
{
    protected static string $resource = ShiftsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
