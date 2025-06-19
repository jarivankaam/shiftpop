<?php

namespace App\Filament\Resources\RequestsResource\Pages;

use App\Filament\Resources\RequestsResource;
use App\Models\Shift;
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

    protected function afterSave(): void
    {
        $request = $this->record;

        if (
            $request->type === 'takeover' &&
            $request->status === 'approved' &&
            $request->shift_id
        ) {
            $shift = Shift::find($request->shift_id);

            if ($shift) {
                $shift->user_id = $request->user_id;
                $shift->save();

                // Optional: Flash success or log
                \Log::info("Shift ID {$shift->id} reassigned to user ID {$request->user_id} due to takeover approval.");
            }
        }
    }
}
