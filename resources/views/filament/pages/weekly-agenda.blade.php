<x-filament::page>
    <div class="space-y-6">
        @foreach ($agenda as $day => $data)
            @php
                $shifts = $data['shifts'];
                $isDayOff = $data['is_day_off'];
                $isSick = $data['is_sick'];
            @endphp

            <div class="border p-4 rounded-md bg-white dark:bg-gray-800">
                <h2 class="text-lg font-semibold text-primary-600">{{ $day }}</h2>

                @if ($isSick)
                    <p class="text-sm text-red-600 italic">Ziek</p>
                @elseif ($isDayOff)
                    <p class="text-sm text-green-600 italic">Vrij</p>
                @elseif ($shifts->isEmpty())
                    <p class="text-sm text-gray-500 italic">Geen diensten</p>
                @else
                    <ul class="mt-2 space-y-1">
                        @foreach ($shifts as $shift)
                            <li>
                <span class="font-mono text-sm">
                    {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}
                    →
                    {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                </span>
                                — {{ $shift->title ?? 'Dienst' }}
                            </li>
                        @endforeach
                    </ul>
                @endif

            </div>
        @endforeach
    </div>
</x-filament::page>
