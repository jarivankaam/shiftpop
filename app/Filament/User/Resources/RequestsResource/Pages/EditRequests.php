<?php

namespace App\Filament\User\Resources\RequestsResource\Pages;

use App\Filament\User\Resources\RequestsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRequests extends EditRecord
{
    protected static string $resource = RequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
