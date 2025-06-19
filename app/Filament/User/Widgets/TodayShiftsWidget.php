<?php

namespace App\Filament\User\Widgets;


use Carbon\Carbon;
use App\Models\Shift;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class TodayShiftsWidget extends Widget
{
    protected static string $view = 'filament.User.widgets.today-shifts-widget';
    protected int | string | array $columnSpan = 'full'; // or 'md' if used in grid

    public $todayShifts = [];
    public $todayDateFormatted;

    public function mount(): void
    {
        Carbon::setLocale('nl');
        $user = \Filament\Facades\Filament::auth()->user();

        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        $this->todayDateFormatted = ucfirst(now()->translatedFormat('l j F Y'));

        $this->todayShifts = Shift::where('user_id', $user->id)
            ->whereBetween('start_time', [$today, $tomorrow])
            ->orderBy('start_time')
            ->get();
    }
}
