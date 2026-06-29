@php
    $typeLabel = $type == 3 ? 'MM 3D' : 'MM 2D';
@endphp

@extends('admin.master')

@section('content')
    <div class="container-fluid">
        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">LOTTERY CALENDAR</p>
                <h1>{{ $typeLabel }} Calendar</h1>
            </div>
        </div>

        <nav class="report-draw-tabs" aria-label="Calendar type navigation">
            <a class="{{ $type == 2 ? 'active' : '' }}" href="{{ route('admin.lotteries.records', ['type' => 2]) }}">
                <i class="fas fa-calendar-alt"></i>
                MM 2D
            </a>
            <a class="{{ $type == 3 ? 'active' : '' }}" href="{{ route('admin.lotteries.records', ['type' => 3]) }}">
                <i class="fas fa-dice-three"></i>
                MM 3D
            </a>
        </nav>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">RESULT HISTORY</p>
                    <h2>{{ $typeLabel }} published results</h2>
                    <p class="panel-subtitle">{{ number_format($records->total()) }} published result records</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Clock</th>
                            <th>Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            @php
                                $hour = $record->clock->hour < 10 ? '0' . $record->clock->hour : $record->clock->hour;
                                $minute = $record->clock->minute < 10 ? '0' . $record->clock->minute : $record->clock->minute;
                            @endphp
                            <tr>
                                <td>
                                    <strong class="table-primary-line">{{ $record->created_at->format('M d, Y') }}</strong>
                                    <small class="table-secondary-line">{{ $record->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <span class="clock-pill">{{ $hour }}:{{ $minute }} {{ $record->clock->morning == 1 ? 'AM' : 'PM' }}</span>
                                </td>
                                <td><span class="lottery-number">{{ $record->number }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3"><span class="muted">No calendar records found.</span></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $records->links() }}
        </section>
    </div>
@endsection
