<x-filament::widget>
    <x-filament::card>
        <h2 class="text-lg font-bold mb-2">
            📆 Vandaag: {{ $todayDateFormatted }}
        </h2>

        @if ($todayShifts->isEmpty())
            <p class="text-gray-500 italic">Geen diensten vandaag.</p>
        @else
            <ul class="space-y-1">
                @foreach ($todayShifts as $shift)
                    <li>
                        🕒
                        {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}
                        →
                        {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                        — {{ $shift->title ?? 'Dienst' }}
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::card>
</x-filament::widget>
