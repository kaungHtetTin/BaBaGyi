@php
    $lottery_hour = $clock->hour < 10 ? '0' . $clock->hour : $clock->hour;
    $lottery_minute = $clock->minute < 10 ? '0' . $clock->minute : $clock->minute;
    $drawLabel = $lottery_type->type . ' ' . $lottery_hour . ':' . $lottery_minute . ' ' . ($clock->morning == 1 ? 'AM' : 'PM');
    $backUrl = route('admin.numbers', [
        'lottery_type_id' => $lottery_type->id,
        'clock_id' => $clock->id,
    ]);
@endphp

@extends('admin.master')
@section('content')
    <div class="container-fluid">
        @if (session('msg'))
            <div class="alert alert-success">
                {{ session('msg') }}
            </div>
        @endif

        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">NUMBER CONTROL</p>
                <h1>Edit number</h1>
            </div>
            <a class="btn secondary" href="{{ $backUrl }}">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">SELL LIMIT</p>
                    <h2><span class="lottery-number">{{ $number->number }}</span> <span class="clock-pill">{{ $drawLabel }}</span></h2>
                    <p class="panel-subtitle">Update the sell amount for this number only.</p>
                </div>
                @if ($number->disable == 1)
                    <span class="status status-danger"><span class="status-dot"></span>Disabled</span>
                @else
                    <span class="status status-success"><span class="status-dot"></span>Active</span>
                @endif
            </div>

            <div class="number-edit-summary">
                <div>
                    <span>Demand amount</span>
                    <strong>{{ number_format($number->demand) }} MMK</strong>
                </div>
                <div>
                    <span>Report amount</span>
                    <strong>{{ number_format($number->report) }} MMK</strong>
                </div>
            </div>

            <form class="settings-form" action="{{ route('admin.numbers.modify', $number->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="settings-form-grid compact">
                    <label class="form-field">
                        <span>Sell amount</span>
                        <input type="text" placeholder="Sell amount" name="sell" value="{{ old('sell', $number->sell) }}">
                        @error('sell')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>
                </div>

                <div class="settings-form-actions">
                    <a class="btn secondary" href="{{ $backUrl }}">Cancel</a>
                    <button class="btn primary" type="submit">
                        <i class="fas fa-save"></i>
                        Update sell amount
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
