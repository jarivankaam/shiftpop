<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Agenda extends Page
{
    protected static string $view = 'filament.user.pages.agenda';
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected Carbon $startOfWeek;
    protected $shifts;
    protected $days;

    public function mount(): void
    {
        $weekParam = request()->query('week');
        $this->startOfWeek = $weekParam
            ? Carbon::parse($weekParam)->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);

        $this->loadShifts();
        $this->generateDays();
    }

    public function loadShifts(): void
    {
        $endOfWeek = $this->startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

        $query = Shift::with('user')
            ->whereBetween('start_time', [$this->startOfWeek->startOfDay(), $endOfWeek->endOfDay()]);

        if (request()->is('user/*')) {
            $query->where('user_id', Auth::id());
        }

        $this->shifts = $query->get()
            ->groupBy(fn($shift) => Carbon::parse($shift->start_time)->format('Y-m-d'));
    }

    public function generateDays(): void
    {
        $this->days = collect();

        for ($i = 0; $i < 7; $i++) {
            $this->days->push($this->startOfWeek->copy()->addDays($i));
        }
    }

    public function getViewData(): array
    {
        return [
            'startOfWeek' => $this->startOfWeek,
            'days' => $this->days,
            'shifts' => $this->shifts,
        ];
    }
}
