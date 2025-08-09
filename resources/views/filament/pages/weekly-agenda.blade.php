<x-filament::page>
    @php
        // -----------------------------
        // Query state
        // -----------------------------
        $currentWeek = (int) request()->query('week', 0);
        $view = request()->query('view', 'week'); // 'week' | 'agenda' | 'calendar'

        // Week boundaries (voor week/agenda)
        $weekStart = \Carbon\Carbon::now()->startOfWeek()->addWeeks($currentWeek);
        $weekEnd   = \Carbon\Carbon::now()->endOfWeek()->addWeeks($currentWeek);

        // Maand parsing (voor calendar)
        $monthParam = request()->query('month'); // 'YYYY-MM' of null
        if ($monthParam) {
            try {
                $monthStart = \Carbon\Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
            } catch (\Exception $e) {
                $monthStart = \Carbon\Carbon::now()->startOfMonth();
            }
        } else {
            // Als geen ?month is meegegeven, baseer maand op weekStart
            $monthStart = $weekStart->copy()->startOfMonth();
        }
        $monthEnd = $monthStart->copy()->endOfMonth();

        // Grid-interval voor de kalender (start maandag, eind zondag)
        $calendarStart = $monthStart->copy()->startOfWeek();
        $calendarEnd   = $monthEnd->copy()->endOfWeek();

        // -----------------------------
        // Agenda/Week -> vlakke items incl. Vrij/Ziek (zoals eerder)
        // -----------------------------
        $flat = collect();
        $i = 0;
        foreach ($agenda as $dayLabel => $data) {
            $dateObj   = $weekStart->copy()->addDays($i);
            $dateStr   = $dateObj->toDateString();

            $isSick    = $data['is_sick'] ?? false;
            $isDayOff  = $data['is_day_off'] ?? false;
            /** @var \Illuminate\Support\Collection $shifts */
            $shifts    = ($data['shifts'] ?? collect()) ?: collect();

            if ($isSick) {
                $flat->push([
                    'type'      => 'sick',
                    'dayLabel'  => $dayLabel,
                    'date'      => $dateStr,
                    'dateHuman' => $dateObj->isoFormat('dddd D MMMM YYYY'),
                    'title'     => 'Ziek',
                    'start'     => null,
                    'end'       => null,
                    'start_at'  => $dateObj->copy()->startOfDay(),
                ]);
            } elseif ($isDayOff) {
                $flat->push([
                    'type'      => 'off',
                    'dayLabel'  => $dayLabel,
                    'date'      => $dateStr,
                    'dateHuman' => $dateObj->isoFormat('dddd D MMMM YYYY'),
                    'title'     => 'Vrij',
                    'start'     => null,
                    'end'       => null,
                    'start_at'  => $dateObj->copy()->startOfDay(),
                ]);
            }

            if ($shifts->isNotEmpty()) {
                foreach ($shifts as $shift) {
                    $start = \Carbon\Carbon::parse($shift->start_time);
                    $end   = \Carbon\Carbon::parse($shift->end_time);
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
            }

            $i++;
        }
        $agendaItems = $flat->sortBy('start_at')->groupBy('date');

        // -----------------------------
        // Calendar (maand) data
        // -----------------------------
        // Verwacht idealiter: $eventsByDate (array/collectie) -> ['YYYY-MM-DD' => [events...]]
        // Als je controller dit (nog) niet levert, vul in elk geval de huidige week in de juiste datums:
        /** @var \Illuminate\Support\Collection $eventsByDate */
        $eventsByDate = collect();

        if (isset($calendarEventsByDate) && $calendarEventsByDate) {
            // Als je in je controller $calendarEventsByDate meegeeft (zelfde structuur als hieronder)
            $eventsByDate = collect($calendarEventsByDate);
        } else {
            // fallback: gebruik de reeds berekende $flat van de huidige week
            foreach ($flat as $item) {
                $eventsByDate->push($item);
            }
            $eventsByDate = $eventsByDate->groupBy('date');
        }

        // Maak een periode over alle dagen in de kalender-grid
        $period = \Carbon\CarbonPeriod::create($calendarStart, '1 day', $calendarEnd);
    @endphp

    <div class="space-y-6">
        {{-- Navigatie --}}
        <div class="flex flex-wrap justify-between items-center mb-6 gap-2">
            {{-- Links: Prev/Today/Next --}}
            <div class="flex gap-2">
                @if ($view === 'calendar')
                    @php
                        $prevMonth = $monthStart->copy()->subMonth()->format('Y-m');
                        $nextMonth = $monthStart->copy()->addMonth()->format('Y-m');
                        $thisMonth = \Carbon\Carbon::now()->format('Y-m');
                    @endphp
                    <a href="?view=calendar&month={{ $prevMonth }}"
                       class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 transition">← Vorige maand</a>

                    <a href="?view=calendar&month={{ $thisMonth }}"
                       class="px-4 py-2 bg-blue-100 text-blue-700 font-semibold rounded hover:bg-blue-200 transition">📅 Deze maand</a>

                    <a href="?view=calendar&month={{ $nextMonth }}"
                       class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 transition">Volgende maand →</a>
                @else
                    <a href="?week={{ $currentWeek - 1 }}&view={{ $view }}"
                       class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 transition">← Vorige week</a>

                    <a href="?week=0&view={{ $view }}"
                       class="px-4 py-2 bg-blue-100 text-blue-700 font-semibold rounded hover:bg-blue-200 transition">📅 Deze week</a>

                    <a href="?week={{ $currentWeek + 1 }}&view={{ $view }}"
                       class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 transition">Volgende week →</a>
                @endif
            </div>

            {{-- Rechts: View-toggle --}}
            <div class="flex gap-1">
                <a href="?week={{ $currentWeek }}&view=week"
                   class="px-3 py-2 rounded transition {{ $view === 'week' ? 'bg-primary-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                    Weekoverzicht
                </a>
                <a href="?week={{ $currentWeek }}&view=agenda"
                   class="px-3 py-2 rounded transition {{ $view === 'agenda' ? 'bg-primary-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                    Agenda
                </a>
                <a href="?view=calendar&month={{ $monthStart->format('Y-m') }}"
                   class="px-3 py-2 rounded transition {{ $view === 'calendar' ? 'bg-primary-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                    Calendar
                </a>
            </div>
        </div>

        {{-- Onderkop: periode --}}
        <div class="text-center text-sm text-gray-600 mb-4">
            @if ($view === 'calendar')
                Maand: {{ $monthStart->isoFormat('MMMM YYYY') }}
            @else
                Week van {{ $weekStart->format('d M Y') }} tot {{ $weekEnd->format('d M Y') }}
            @endif
        </div>

        {{-- CALENDAR (maand) VIEW --}}
        @if ($view === 'calendar')
            <div class="bg-white dark:bg-gray-800 rounded-md p-4">
                {{-- Weekdag labels --}}
                <div class="grid grid-cols-7 text-xs font-semibold text-gray-500 mb-2">
                    @foreach (['Ma','Di','Wo','Do','Vr','Za','Zo'] as $wd)
                        <div class="px-2 py-1">{{ $wd }}</div>
                    @endforeach
                </div>

                {{-- Maand-grid: chunk per 7 dagen --}}
                <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 rounded-md overflow-hidden">
                    @php
                        $days = collect($period->toArray());
                        $rows = $days->chunk(7);
                    @endphp

                    @foreach ($rows as $weekRow)
                        @foreach ($weekRow as $day)
                            @php
                                /** @var \Carbon\Carbon $day */
                                $inMonth = $day->between($monthStart, $monthEnd);
                                $dayKey  = $day->toDateString();
                                $items   = $eventsByDate->get($dayKey, collect());
                            @endphp

                            <div class="bg-white dark:bg-gray-900 min-h-[110px] p-2 flex flex-col">
                                <div class="flex items-center justify-between">
                                    <div class="text-xs {{ $inMonth ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400' }}">
                                        {{ $day->format('j') }}
                                    </div>
                                    {{-- vandaag badge --}}
                                    @if ($day->isToday())
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-100 text-blue-700">vandaag</span>
                                    @endif
                                </div>

                                {{-- events --}}
                                <div class="mt-1 space-y-1">
                                    @foreach ($items as $item)
                                        @php
                                            $dotClass = 'bg-gray-400';
                                            $label = $item['title'] ?? 'Item';
                                            if (($item['type'] ?? null) === 'off')  { $dotClass = 'bg-emerald-500'; $label = 'Vrij'; }
                                            if (($item['type'] ?? null) === 'sick') { $dotClass = 'bg-rose-500';    $label = 'Ziek'; }
                                        @endphp
                                        <div class="flex items-center gap-2 text-xs leading-snug">
                                            <span class="w-2 h-2 rounded-full {{ $dotClass }} shrink-0"></span>
                                            <span class="truncate">
                                                @if (($item['type'] ?? null) === 'shift' && isset($item['start'], $item['end']))
                                                    <span class="font-mono">{{ $item['start'] }}–{{ $item['end'] }}</span>
                                                    <span class="text-gray-600">·</span>
                                                @endif
                                                {{ $label }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- hint voor lege cellen buiten maand --}}
                                @if (!$inMonth && $items->isEmpty())
                                    <div class="mt-auto text-[10px] text-gray-400">—</div>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>

        {{-- AGENDA VIEW --}}
        @elseif ($view === 'agenda')
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
                                    @php
                                        $dotClass = 'bg-gray-400';
                                        if ($item['type'] === 'off')  $dotClass = 'bg-emerald-500';
                                        if ($item['type'] === 'sick') $dotClass = 'bg-rose-500';
                                    @endphp

                                    <li class="py-2 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full {{ $dotClass }} inline-block"></span>
                                            <div>
                                                <div class="font-medium">{{ $item['title'] }}</div>
                                                <div class="text-xs text-gray-500">{{ $item['dayLabel'] }}</div>
                                            </div>
                                        </div>

                                        <div class="font-mono text-sm text-right">
                                            @if ($item['type'] === 'shift')
                                                {{ $item['start'] }} → {{ $item['end'] }}
                                            @else
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

        {{-- WEEK VIEW (origineel) --}}
        @else
            <div class="space-y-6">
                @foreach ($agenda as $day => $data)
                    @php
                        /** @var \Illuminate\Support\Collection $shifts */
                        $shifts = $data['shifts'] ?? collect();
                        $isDayOff = $data['is_day_off'] ?? false;
                        $isSick = $data['is_sick'] ?? false;
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

    {{-- Tips voor controller integratie (optioneel laten staan of verwijderen) --}}
    {{--
        In je controller kun je voor de kalender maandbreed data vullen:
        $month = request('month', now()->format('Y-m'));
        $monthStart = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        // Bouw $calendarEventsByDate = [
        //   'YYYY-MM-DD' => [
        //       ['type' => 'shift', 'title' => 'Dienst', 'start' => '09:00', 'end' => '17:00'],
        //       ['type' => 'off', 'title' => 'Vrij'],
        //       ['type' => 'sick', 'title' => 'Ziek'],
        //   ],
        //   ...
        // ];
        // return view(..., compact('agenda', 'calendarEventsByDate'));
    --}}
</x-filament::page>
