<div class="p-6">
    <div class="flex justify-between items-center mb-4">
        <button wire:click="goToPreviousMonth" class="text-sm bg-gray-200 px-3 py-1 rounded">← Previous</button>
        <h2 class="text-lg font-bold">{{ $currentMonthName }}</h2>
        <button wire:click="goToNextMonth" class="text-sm bg-gray-200 px-3 py-1 rounded">Next →</button>
    </div>

    <div class="grid grid-cols-7 gap-2">
        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
            <div class="text-center font-semibold text-gray-700">{{ $day }}</div>
        @endforeach

        @foreach($days as $day)
            <div class="border p-2 h-28 overflow-y-auto bg-white rounded shadow-sm">
                <div class="text-xs font-bold text-gray-600">
                    {{ $day->format('j M') }}
                </div>

                @foreach($shifts[$day->format('Y-m-d')] ?? [] as $shift)
                    <div class="mt-1 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                        {{ $shift->user->name ?? 'Unassigned' }}<br>
                        {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
