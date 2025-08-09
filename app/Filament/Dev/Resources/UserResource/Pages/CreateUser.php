<?php

namespace App\Filament\Dev\Resources\UserResource\Pages;

use Illuminate\Support\Facades\Password;
use App\Filament\Dev\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

     protected function afterCreate(): void
    {
        $user = $this->record;

        // Send reset link email
        $status = Password::sendResetLink(['email' => $user->email]);

        // (Optional) show a Filament toast about result
        if ($status !== Password::RESET_LINK_SENT) {
            $this->notify('danger', __($status));
        } else {
            $this->notify('success', 'Password setup email sent.');
        }
    }
}
