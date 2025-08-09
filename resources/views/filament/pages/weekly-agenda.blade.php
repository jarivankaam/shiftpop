<x-filament::page>
    @php
        // ---- Query params / view state ----
        $currentWeek  = (int) request()->query('week', 0);
        $currentMonth = (int) request()->query('month', 0);
        $view = request()->query('view', 'week'); // 'week' | 'agenda' | 'calendar'

        // ---- Week range (used by Week/Agenda) ----
        $weekStart = \Carbon\Carbon::now()->startOfWeek()->addWeeks($currentWeek);
        $weekEnd   = \Carbon\Carbon::now()->endOfWeek()->addWeeks($currentWeek);

        // ---- Build Agenda items (incl. Vrij/Ziek) for the selected week ----
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

        // ---- Month grid (for Calendar view) ----
        // We use an optional $calendar array passed from the controller: ['Y-m-d' => ['is_sick'=>..., 'is_day_off'=>..., 'shifts'=>Collection]]
        $monthAnchor = \Carbon\Carbon::now()->startOfMonth()->addMonths($currentMonth);
        $monthLabel  = $monthAnchor->isoFormat('MMMM YYYY');

        $gridStart = $monthAnchor->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
        $gridEnd   = $monthAnchor->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
        $days = [];
        $cursor = $gridStart->copy();
        while ($cursor->lte($gridEnd)) {
            $days[] = $cursor->copy();
            $cursor->addDay();
        }

        // Ensure we always render 6 rows (42 cells) for a stable grid
        if (count($days) < 42) {
            $extra = 42 - count($days);
            for ($k = 0; $k < $extra; $k++) {
                $days[] = $cursor->copy();
                $cursor->addDay();
            }
        }

        // Events map for month view (safe default when $calendar is absent)
        /** @var array<string, array{is_sick?:bool,is_day_off?:bool,shifts?:\Illuminate\Support\Collection}> $calendar */
        $calendar = isset($calendar) && is_array($calendar) ? $calendar : [];
    @endphp

    <div class="space-y-6">
        {{-- Navigation --}}
        <div class="flex flex-wrap justify-between items-center mb-6 gap-2">
            {{-- Left: prev/this/next depending on view --}}
            <div class="flex gap-2">
                @if ($view === 'calendar')
                    <a href="?month={{ $currentMonth - 1 }}&view=calendar"
                       class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 transition">← Vorige maand</a>

                    <a href="?month=0&view=calendar"
                       class="px-4 py-2 bg-blue-100 text-blue-700 font-semibold rounded hover:bg-blue-200 transition">📅 Deze maand</a>

                    <a href="?month={{ $currentMonth + 1 }}&view=calendar"
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

            {{-- Right: view toggles --}}
            <div class="flex gap-1">
                <a href="?week={{ $currentWeek }}&view=week"
                   class="px-3 py-2 rounded transition {{ $view === 'week' ? 'bg-primary-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                    Week
                </a>
                <a href="?week={{ $currentWeek }}&view=agenda"
                   class="px-3 py-2 rounded transition {{ $view === 'agenda' ? 'bg-primary-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                    Agenda
                </a>
                <a href="?month={{ $currentMonth }}&view=calendar"
                   class="px-3 py-2 rounded transition {{ $view === 'calendar' ? 'bg-primary-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                    Kalender
                </a>
            </div>
        </div>

        {{-- Header helper text --}}
        @if ($view === 'calendar')
            <div class="text-center text-sm text-gray-600 mb-4">
                {{ $monthLabel }}
            </div>
        @else
            <div class="text-center text-sm text-gray-600 mb-4">
                Week van {{ $weekStart->format('d M Y') }} tot {{ $weekEnd->format('d M Y') }}
            </div>
        @endif

        {{-- CALENDAR VIEW --}}
        @if ($view === 'calendar')
            <div class="rounded-md bg-white dark:bg-gray-800 p-4">
                {{-- Weekday headers (Mon-Sun) --}}
                <div class="grid grid-cols-7 text-xs font-medium text-gray-500 mb-2">
                    <div class="py-2 text-center">Ma</div>
                    <div class="py-2 text-center">Di</div>
                    <div class="py-2 text-center">Wo</div>
                    <div class="py-2 text-center">Do</div>
                    <div class="py-2 text-center">Vr</div>
                    <div class="py-2 text-center">Za</div>
                    <div class="py-2 text-center">Zo</div>
                </div>

                <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 rounded">
                    @foreach ($days as $d)
                        @php
                            $dateKey = $d->toDateString();
                            $inMonth = $d->isSameMonth($monthAnchor);
                            $cellData = $calendar[$dateKey] ?? ['is_sick' => false, 'is_day_off' => false, 'shifts' => collect()];
                            $isSick = $cellData['is_sick'] ?? false;
                            $isOff  = $cellData['is_day_off'] ?? false;
                            /** @var \Illuminate\Support\Collection $cellShifts */
                            $cellShifts = ($cellData['shifts'] ?? collect()) ?: collect();

                            $maxLines = 3; // show at most 3 lines per cell
                            $extraCount = max(0, $cellShifts->count() - $maxLines);
                        @endphp

                        <div class="bg-white dark:bg-gray-900 min-h-[110px] p-2 flex flex-col {{ $inMonth ? '' : 'opacity-50' }}">
                            <div class="flex items-center justify-between mb-1">
                                <div class="text-xs font-semibold">{{ $d->day }}</div>
                                {{-- indicators --}}
                                <div class="flex gap-1">
                                    @if ($isOff)
                                        <span title="Vrij" class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                                    @endif
                                    @if ($isSick)
                                        <span title="Ziek" class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span>
                                    @endif
                                </div>
                            </div>

                            {{-- Shifts list --}}
                            <div class="space-y-0.5">
                                @foreach ($cellShifts->take($maxLines) as $shift)
                                    @php
                                        $s = \Carbon\Carbon::parse($shift->start_time);
                                        $e = \Carbon\Carbon::parse($shift->end_time);
                                    @endphp
                                    <div class="rounded px-1 py-0.5 text-[11px] bg-gray-100 dark:bg-gray-800">
                                        <span class="font-mono">{{ $s->format('H:i') }}–{{ $e->format('H:i') }}</span>
                                        <span class="ml-1">{{ $shift->title ?? 'Dienst' }}</span>
                                    </div>
                                @endforeach

                                @if ($extraCount > 0)
                                    <div class="text-[11px] text-gray-500">+{{ $extraCount }} meer…</div>
                                @endif

                                @if ($cellShifts->isEmpty() && !$isOff && !$isSick)
                                    <div class="text-[11px] text-gray-400">—</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Legend --}}
                <div class="flex gap-3 text-xs text-gray-600 justify-end mt-3">
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Vrij
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span> Ziek
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-3 h-3 rounded bg-gray-200 border border-gray-300 inline-block"></span> Dienst
                    </span>
                </div>
            </div>

        {{-- AGENDA VIEW --}}
        @elseif ($view === 'agenda')
            @if ($agendaItems->isEmpty())
                <div class="text-center text-gray-500 italic">Geen items in deze periode.</div>
            @else
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
        @else
            {{-- WEEK VIEW (oorspronkelijke weergave) --}}
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
</x-filament::page>
