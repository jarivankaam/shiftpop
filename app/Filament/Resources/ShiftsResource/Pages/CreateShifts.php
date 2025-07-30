<?php

namespace App\Filament\Resources\ShiftsResource\Pages;

use App\Filament\Resources\ShiftsResource;
use App\Models\Shift;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

class CreateShifts extends CreateRecord
{
    protected static string $resource = ShiftsResource::class;

    protected function afterCreate(): void
    {
        $shift = $this->record;

        if ($shift->is_recurring && $shift->recurring_weeks) {
            for ($i = 1; $i <= $shift->recurring_weeks; $i++) {
                Shift::create([
                    'user_id'     => $shift->user_id,
                    'start_time'  => Carbon::parse($shift->start_time)->addWeeks($i),
                    'end_time'    => Carbon::parse($shift->end_time)->addWeeks($i),
                    'is_recurring' => false,
                    'recurring_weeks' => null,
                ]);
            }
        }
    }
}
