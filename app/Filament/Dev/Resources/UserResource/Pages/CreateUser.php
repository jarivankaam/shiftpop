<?php

namespace App\Filament\Dev\Resources\UserResource\Pages;

use Illuminate\Support\Facades\Password;
use App\Filament\Dev\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $user = $this->record;

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            Notification::make()
                ->title('Password setup email sent.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('Could not send reset email'))
                ->body(__($status))
                ->danger()
                ->send();
        }
    }
}
