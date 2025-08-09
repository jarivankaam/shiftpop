<x-filament::page>
    @php
        use Carbon\Carbon;

        $currentWeek = (int) request()->query('week', 0);
        $view = request()->query('view', 'week'); // 'week' | 'agenda'

        $weekStart = Carbon::now()->startOfWeek()->addWeeks($currentWeek);
        $weekEnd   = Carbon::now()->endOfWeek()->addWeeks($currentWeek);

        // ---- NIEUW: bouw agenda-items incl. Ziek/Vrij ----
        $flat = collect();
        $i = 0;
        foreach ($agenda as $dayLabel => $data) {
            $dateObj   = $weekStart->copy()->addDays($i);
            $dateStr   = $dateObj->toDateString();

            $isSick    = $data['is_sick'] ?? false;
            $isDayOff  = $data['is_day_off'] ?? false;
            $shifts    = ($data['shifts'] ?? collect()) ?: collect();

            if ($isSick) {
                // Volledige dag ziek
                $flat->push([
                    'type'      => 'sick',
                    'dayLabel'  => $dayLabel,
                    'date'      => $dateStr,
                    'dateHuman' => $dateObj->isoFormat('dddd D MMMM YYYY'),
                    'title'     => 'Ziek',
                    'start'     => null,
                    'end'       => null,
                    'start_at'  => $dateObj->startOfDay(),
                ]);
            } elseif ($isDayOff) {
                // Volledige dag vrij
                $flat->push([
                    'type'      => 'off',
                    'dayLabel'  => $dayLabel,
                    'date'      => $dateStr,
                    'dateHuman' => $dateObj->isoFormat('dddd D MMMM YYYY'),
                    'title'     => 'Vrij',
                    'start'     => null,
                    'end'       => null,
                    'start_at'  => $dateObj->startOfDay(),
                ]);
            } elseif ($shifts->isNotEmpty()) {
                foreach ($shifts as $shift) {
                    $start = Carbon::parse($shift->start_time);
                    $end   = Carbon::parse($shift->end_time);
                    $flat->push([
                        'type'      => 'shift',
                        'dayLabel'  => $dayLabel,
                        'date'      => $start->toDateString(),
                        'dateHuman' => $start->isoFormat('dddd D MMMM YYYY'),
                        'title'     => $shift->title ?? 'Dienst',
                        'start'     => $start->format('H:i'),
                        'end'       => $end->format('H:i'),
                        'start_at'  => $start,
                    ]);
                }
            } else {
                // Geen diensten, geen vrij/ziek → niets tonen (of maak er een "Geen diensten" event van als je wilt)
            }

            $i++;
        }

        $agendaItems = $flat->sortBy('start_at')->groupBy('date');
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap justify-between items-center mb-6 gap-2">
            <div class="flex gap-2">
                <a href="?week={{ $currentWeek - 1 }}&view={{ $view }}"
                   class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 transition">← Vorige week</a>

                <a href="?week=0&view={{ $view }}"
                   class="px-4 py-2 bg-blue-100 text-blue-700 font-semibold rounded hover:bg-blue-200 transition">📅 Deze week</a>

                <a href="?week={{ $currentWeek + 1 }}&view={{ $view }}"
                   class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 transition">Volgende week →</a>
            </div>

            <div class="flex gap-1">
                <a href="?week={{ $currentWeek }}&view=week"
                   class="px-3 py-2 rounded transition {{ $view === 'week' ? 'bg-primary-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                    Weekoverzicht
                </a>
                <a href="?week={{ $currentWeek }}&view=agenda"
                   class="px-3 py-2 rounded transition {{ $view === 'agenda' ? 'bg-primary-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                    Agenda
                </a>
            </div>
        </div>

        <div class="text-center text-sm text-gray-600 mb-4">
            Week van {{ $weekStart->format('d M Y') }} tot {{ $weekEnd->format('d M Y') }}
        </div>

        @if ($view === 'agenda')
            {{-- AGENDA VIEW met Vrij/Ziek --}}
            @if ($agendaItems->isEmpty())
                <div class="text-center text-gray-500 italic">Geen items in deze periode.</div>
            @else
                {{-- Legend --}}
                <div class="flex gap-3 text-xs text-gray-600 justify-end">
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Vrij
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span> Ziek
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-gray-400 inline-block"></span> Dienst
                    </span>
                </div>

                <div class="space-y-6">
                    @foreach ($agendaItems as $date => $items)
                        <div class="border p-4 rounded-md bg-white dark:bg-gray-800">
                            <h2 class="text-lg font-semibold text-primary-600">
                                {{ $items->first()['dateHuman'] }}
                            </h2>

                            <ul class="mt-2 divide-y">
                                @foreach ($items as $item)
                                    <li class="py-2 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            @php
                                                $dot = match($item['type']) {
                                                    'off'   => 'bg-emerald-500',
                                                    'sick'  => 'bg-rose-500',
                                                    default => 'bg-gray-400',
                                                };
                                            @endphp
                                            <span class="w-2.5 h-2.5 rounded-full {{ $dot }} inline-block"></span>
                                            <div>
                                                <div class="font-medium">
                                                    {{ $item['title'] }}
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $item['dayLabel'] }}</div>
                                            </div>
                                        </div>

                                        <div class="font-mono text-sm text-right">
                                            @if ($item['type'] === 'shift')
                                                {{ $item['start'] }} → {{ $item['end'] }}
                                            @elseif ($item['type'] === 'off')
                                                Hele dag
                                            @elseif ($item['type'] === 'sick')
                                                Hele dag
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            {{-- WEEK VIEW (ongewijzigd) --}}
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
        @endif
    </div>
</x-filament::page>
