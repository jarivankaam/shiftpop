<?php

namespace App\Filament\Dev\Resources\UserResource\Pages;

use App\Filament\Dev\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
