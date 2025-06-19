<x-filament::page>
    <div class="bg-white text-black rounded-lg p-6 shadow-md">
        <div class="flex justify-between items-center mb-4">
            <div class="flex gap-3">
                <a href="{{ url()->current() . '?week=' . now()->startOfWeek()->subWeek()->toDateString() }}"
                   class="bg-white text-black font-bold px-4 py-2 rounded border border-gray-300">
                    vorige week
                </a>
                <a href="{{ url()->current() . '?week=' . now()->startOfWeek()->addWeek()->toDateString() }}"
                   class="bg-white text-black font-bold px-4 py-2 rounded border border-gray-300">
                    volgende week
                </a>
            </div>
            <div class="flex gap-3">
                <button class="bg-primary-500 text-white font-bold px-4 py-2 rounded">Ruilen of dienst overnemen</button>
                <button class="bg-danger-500 text-white font-bold px-4 py-2 rounded">Vrij vragen</button>
            </div>
        </div>

        <h2 class="text-3xl font-extrabold text-primary-600 mb-4">Rooster</h2>
        <p class="text-sm mb-4">Week #{{ $startOfWeek->isoWeek() }}</p>

        <div class="grid grid-cols-7 gap-3 text-center">
            @foreach ($days as $day)
                @php
                    $dateKey = $day->format('Y-m-d');
                    $dayName = \Illuminate\Support\Str::lower($day->locale('nl')->isoFormat('dddd'));
                    $shiftsForDay = $shifts->get($dateKey) ?? collect();
                    $hasOwnShift = $shiftsForDay->contains(fn($shift) => $shift->user_id === auth()->id());
                    $hasAnyShift = $shiftsForDay->isNotEmpty();
                @endphp

                <div
                    @class([
                        'rounded-lg p-2 shadow text-white',
                        'bg-warning-500' => $hasOwnShift,
                        'bg-danger-500' => !$hasAnyShift,
                        'bg-gray-100 text-black' => !$hasOwnShift && $hasAnyShift,
                    ])
                >
                    <div class="font-bold capitalize">{{ $dayName }}</div>
                    <div class="text-sm mt-2">{{ $day->format('d-m-Y') }}</div>

                    @forelse ($shiftsForDay as $shift)
                        <div class="text-xs mt-1">
                            {{ $shift->user->name }}<br>
                            {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} -
                            {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                        </div>
                    @empty
                        <div class="mt-2 text-yellow-300 text-sm font-bold">Vrij</div>
                    @endforelse
                </div>
            @endforeach
        </div>
    </div>
</x-filament::page>
