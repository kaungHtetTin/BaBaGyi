@extends('admin.master')

@push('styles')
    <link href="{{ asset('css/admin-calendar.css') }}?v={{ filemtime(public_path('css/admin-calendar.css')) }}" rel="stylesheet">
@endpush

@php
    $typeLabel = $type == 3 ? 'MM 3D' : 'MM 2D';
    $monthParams = ['year' => $selectedMonth->year, 'month' => $selectedMonth->month];
    $calendarUrl = fn ($date) => route('admin.lotteries.records', [
        'type' => $type, 'year' => $date->year, 'month' => $date->month,
    ]);
@endphp

@section('content')
    <div class="container-fluid result-calendar">
        @if (session('history_status'))
            <div class="alert alert-success" role="status">{{ session('history_status') }}</div>
        @endif
        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">LOTTERY CALENDAR</p>
                <h1>{{ $typeLabel }} Calendar</h1>
            </div>
            <span class="calendar-timezone"><i class="far fa-clock" aria-hidden="true"></i> Myanmar time (UTC+06:30)</span>
        </div>

        <nav class="report-draw-tabs" aria-label="Calendar type navigation">
            <a class="{{ $type == 2 ? 'active' : '' }}" href="{{ route('admin.lotteries.records', ['type' => 2] + $monthParams) }}" @if($type == 2) aria-current="page" @endif>
                <i class="fas fa-calendar-alt" aria-hidden="true"></i> MM 2D
            </a>
            <a class="{{ $type == 3 ? 'active' : '' }}" href="{{ route('admin.lotteries.records', ['type' => 3] + $monthParams) }}" @if($type == 3) aria-current="page" @endif>
                <i class="fas fa-dice-three" aria-hidden="true"></i> MM 3D
            </a>
        </nav>

        <section class="panel calendar-panel" aria-labelledby="calendar-month-title">
            <div class="calendar-toolbar">
                <div class="calendar-title">
                    <p class="eyebrow">RESULT HISTORY</p>
                    <h2 id="calendar-month-title">{{ $selectedMonth->format('F Y') }}</h2>
                    <p>{{ number_format($recordCount) }} saved {{ $recordCount === 1 ? 'record' : 'records' }} &middot; {{ $resultDayCount }} {{ $resultDayCount === 1 ? 'day' : 'days' }} with results</p>
                </div>
                <nav class="calendar-navigation" aria-label="Month navigation">
                    @if ($previousMonth->year >= 1900)
                        <a class="calendar-arrow" href="{{ $calendarUrl($previousMonth) }}" aria-label="Previous month: {{ $previousMonth->format('F Y') }}" title="Previous month">
                            <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        </a>
                    @endif
                    <a class="calendar-current" href="{{ $calendarUrl($today) }}">This month</a>
                    @if ($nextMonth->year <= 9999)
                        <a class="calendar-arrow" href="{{ $calendarUrl($nextMonth) }}" aria-label="Next month: {{ $nextMonth->format('F Y') }}" title="Next month">
                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        </a>
                    @endif
                </nav>
            </div>

            <div class="calendar-options">
                <div class="calendar-key"><span class="calendar-today-key" aria-hidden="true"></span> Today <span class="calendar-key-divider" aria-hidden="true">&middot;</span> <span>-- = no saved result</span></div>
                <form class="calendar-jump" method="GET" action="{{ route('admin.lotteries.records') }}" aria-label="Jump to month">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <label class="sr-only" for="calendar-month">Month</label>
                    <select id="calendar-month" name="month">
                        @for ($monthNumber = 1; $monthNumber <= 12; $monthNumber++)
                            <option value="{{ $monthNumber }}" @selected($monthNumber === $selectedMonth->month)>{{ $selectedMonth->month($monthNumber)->format('F') }}</option>
                        @endfor
                    </select>
                    <label class="sr-only" for="calendar-year">Year</label>
                    <input id="calendar-year" type="number" name="year" min="1900" max="9999" value="{{ $selectedMonth->year }}" required>
                    <button type="submit">Go</button>
                </form>
            </div>

            @if ($recordCount === 0)
                <p class="calendar-empty" role="status"><i class="far fa-calendar" aria-hidden="true"></i> No results saved for this month.</p>
            @endif
            <p class="calendar-scroll-hint" id="calendar-scroll-hint"><i class="fas fa-arrows-alt-h" aria-hidden="true"></i> Scroll sideways to see the full week.</p>
            <div class="calendar-scroll" role="region" tabindex="0" aria-label="Monthly results calendar" aria-describedby="calendar-scroll-hint">
                <div class="calendar-grid" role="table" aria-label="{{ $typeLabel }} results for {{ $selectedMonth->format('F Y') }}">
                    <div class="calendar-weekdays" role="row">
                        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                            <div role="columnheader" class="{{ in_array($weekday, ['Sat', 'Sun']) ? 'calendar-weekend-heading' : '' }}">{{ $weekday }}</div>
                        @endforeach
                    </div>
                    @foreach ($calendarWeeks as $week)
                        <div class="calendar-week" role="row">
                            @foreach ($week as $day)
                                <div role="cell" class="calendar-day {{ ! $day['in_month'] ? 'is-outside' : '' }} {{ $day['is_weekend'] ? 'is-weekend' : '' }} {{ $day['is_today'] ? 'is-today' : '' }}"
                                    data-date="{{ $day['date']->toDateString() }}" data-current-month="{{ $day['in_month'] ? 'true' : 'false' }}" aria-label="{{ $day['date']->format('l, F j, Y') }}">
                                    <div class="calendar-day-heading">
                                        <time datetime="{{ $day['date']->toDateString() }}" @if($day['is_today']) aria-current="date" @endif>{{ $day['date']->day }}</time>
                                        @if ($day['is_today']) <span>Today</span> @endif
                                        @if ($day['in_month'] && $day['date']->lte($today))
                                            <a class="calendar-edit-day" href="{{ route('admin.lotteries.edit-day', [
                                                'type' => $type, 'date' => $day['date']->toDateString(), 'source' => 'calendar',
                                            ]) }}" aria-label="Edit history for {{ $day['date']->format('d M Y') }}" title="Edit history">
                                                <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                    </div>
                                    @if ($day['in_month'])
                                        @if (count($day['results']))
                                            <dl class="calendar-results">
                                                @foreach ($day['results'] as $result)
                                                    <div>
                                                        <dt>{{ $result['label'] }}</dt>
                                                        <dd class="{{ $result['number'] === null ? 'is-missing' : '' }}">{{ $result['number'] ?? '--' }}</dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        @else
                                            <p class="calendar-day-empty">{{ $type == 2 && $day['is_weekend'] ? 'No scheduled draw' : 'No saved result' }}</p>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
