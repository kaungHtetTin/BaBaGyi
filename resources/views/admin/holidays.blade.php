@php
    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
@endphp

@extends('admin.master')

@section('content')
    <div class="container-fluid">
        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">OPERATIONS CALENDAR</p>
                <h1>Holidays</h1>
            </div>
        </div>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">CALENDAR RULES</p>
                    <h2>Holiday list</h2>
                    <p class="panel-subtitle">{{ number_format($holidays->count()) }} configured holidays</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Day</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($holidays as $holiday)
                            <tr>
                                <td><strong class="table-primary-line">{{ $holiday->title }}</strong></td>
                                <td>{{ $months[$holiday->month - 1] . ', ' . $holiday->day }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2"><span class="muted">No holidays found.</span></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
