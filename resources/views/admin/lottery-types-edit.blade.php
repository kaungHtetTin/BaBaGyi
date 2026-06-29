@php
    $lottery_hour = $clock->hour < 10 ? '0' . $clock->hour : $clock->hour;
    $lottery_minute = $clock->minute < 10 ? '0' . $clock->minute : $clock->minute;
    $drawLabel = $lottery_hour . ':' . $lottery_minute . ' ' . ($clock->morning == 1 ? 'AM' : 'PM');
    $isAuto = $lottery_type->release_mode == 1;
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
                <p class="eyebrow">LOTTERY CONFIGURATION</p>
                <h1>Edit lottery schedule</h1>
            </div>
            <a class="btn secondary" href="{{ route('admin.lottery-types') }}">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">DRAW SETTINGS</p>
                    <h2>{{ $lottery_type->type }} <span class="clock-pill">{{ $drawLabel }}</span></h2>
                    <p class="panel-subtitle">Adjust the multiplier, close timing, and release mode for this schedule.</p>
                </div>
                <div class="release-toggle {{ $isAuto ? 'is-auto' : 'is-manual' }}" aria-label="{{ $lottery_type->type }} release mode">
                    <a class="{{ $isAuto ? 'active' : '' }}" href="{{ route('admin.lottery-types.release_auto', $lottery_type->id) }}"
                        aria-current="{{ $isAuto ? 'true' : 'false' }}">
                        <i class="fas fa-sync-alt"></i>
                        Auto
                    </a>
                    <a class="{{ !$isAuto ? 'active' : '' }}" href="{{ route('admin.lottery-types.release_manual', $lottery_type->id) }}"
                        aria-current="{{ !$isAuto ? 'true' : 'false' }}">
                        <i class="fas fa-hand-paper"></i>
                        Manual
                    </a>
                </div>
            </div>

            <form class="settings-form" action="{{ route('admin.lottery-types.update', $lottery_clock->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="settings-form-grid">
                    <label class="form-field">
                        <span>Multiplication</span>
                        <input type="text" placeholder="Enter multiplication" name="multiplication"
                            value="{{ old('multiplication', $lottery_type->coefficient) }}">
                        @error('multiplication')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="form-field">
                        <span>Close before (minutes)</span>
                        <input type="text" placeholder="Enter minutes" name="close_before"
                            value="{{ old('close_before', $lottery_clock->close_before) }}">
                        @error('close_before')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>
                </div>

                <div class="settings-form-actions">
                    <a class="btn secondary" href="{{ route('admin.lottery-types') }}">Cancel</a>
                    <button class="btn primary" type="submit">
                        <i class="fas fa-save"></i>
                        Update settings
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
