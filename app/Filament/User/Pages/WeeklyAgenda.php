<?php

namespace App\Filament\User\Pages;

use App\Models\Requests;
use Filament\Pages\Page;
use App\Models\Shift;

class WeeklyAgenda extends Page
{
    protected static ?string $title = 'Mijn werkdagen';
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Mijn werkdagen';
    protected static string $view = 'filament.pages.weekly-agenda';

    public array $agenda = [];

    public function mount(): void
    {
        \Carbon\Carbon::setLocale('nl');

        $user = \Filament\Facades\Filament::auth()->user();

        // ✅ Get week offset from query and cast safely
        $weekOffsetRaw = request()->query('week', '0');
        $weekOffset = is_numeric($weekOffsetRaw) ? (int) $weekOffsetRaw : 0;

        $startOfWeek = \Carbon\Carbon::now()->startOfWeek()->addWeeks($weekOffset);
        $endOfWeek = \Carbon\Carbon::now()->endOfWeek()->addWeeks($weekOffset);

        // Get all shifts for user within the week
        $shifts = Shift::where('user_id', $user->id)
            ->whereBetween('start_time', [$startOfWeek, $endOfWeek])
            ->orderBy('start_time')
            ->get();

        // Approved requests with no shift = day off
        $dayOffRequests = Requests::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereNull('shift_id')
            ->whereBetween('requested_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->pluck('requested_date')
            ->map(fn($date) => \Carbon\Carbon::parse($date)->toDateString());

        // Approved sick days
        $sickDays = Requests::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('type', 'ziek')
            ->whereBetween('requested_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->pluck('requested_date')
            ->map(fn($date) => \Carbon\Carbon::parse($date)->toDateString());

        $agenda = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dayName = ucfirst($day->translatedFormat('l'));
            $dayKey = $day->toDateString();

            $isDayOff = $dayOffRequests->contains($dayKey);
            $isSick = $sickDays->contains($dayKey);

            $agenda[$dayName] = [
                'date' => $dayKey,
                'is_day_off' => $isDayOff,
                'is_sick' => $isSick,
                'shifts' => $shifts->filter(function ($shift) use ($day) {
                    return \Carbon\Carbon::parse($shift->start_time)->isSameDay($day);
                })->values(),
            ];
        }

        $this->agenda = $agenda;
    }
}
