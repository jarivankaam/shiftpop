<?php

namespace App\Filament\User\Pages;

use App\Models\Requests;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use App\Models\Shift;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;

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
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        // All shifts of the user this week
        $shifts = Shift::where('user_id', $user->id)
            ->whereBetween('start_time', [$startOfWeek, $endOfWeek])
            ->orderBy('start_time')
            ->get();

        // Approved requests with no shift_id = day off
        $dayOffRequests = Requests::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereNull('shift_id')
            ->whereBetween('requested_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->pluck('requested_date')
            ->map(fn($date) => \Carbon\Carbon::parse($date)->toDateString());

        // ✅ Approved sick days (regardless of shift)
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
