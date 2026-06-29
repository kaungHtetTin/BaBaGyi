@extends('admin.master')

@section('content')
    <div class="container-fluid">
        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">MANUAL RELEASE</p>
                <h1>Release 2D result</h1>
            </div>
            <a class="btn secondary" href="{{ route('admin.index') }}">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>

        <section class="panel glass release-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">RESULT ENTRY</p>
                    <h2>MM 2D winning number</h2>
                    <p class="panel-subtitle">Enter the confirmed result for the selected draw time.</p>
                </div>
                <span class="status status-danger"><span class="status-dot"></span>Manual mode</span>
            </div>

            <div class="release-warning">
                <span><i class="fas fa-exclamation-triangle"></i></span>
                <div>
                    <strong>Release is final</strong>
                    <p>This will mark winning vouchers, verify losing vouchers, reset demand, and clear hot numbers for this draw.</p>
                </div>
            </div>

            <form class="settings-form release-form" action="{{ route('admins.release-2d') }}" method="POST">
                @csrf

                <div class="settings-form-grid">
                    <label class="form-field">
                        <span>2D lottery number</span>
                        <input id="input_lottery_number" type="text" name="lottery_number" inputmode="numeric" maxlength="2"
                            placeholder="00" value="{{ old('lottery_number') }}">
                        @error('lottery_number')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="form-field">
                        <span>Lottery time</span>
                        <select name="clock_id">
                            @foreach ($lottery_clocks as $lottery_clock)
                                @php
                                    $clock = $lottery_clock->clock;
                                    $lottery_hour = $clock->hour < 10 ? '0' . $clock->hour : $clock->hour;
                                    $lottery_minute = $clock->minute < 10 ? '0' . $clock->minute : $clock->minute;
                                @endphp
                                <option value="{{ $clock->id }}" @selected(old('clock_id') == $clock->id)>
                                    {{ $lottery_hour }}:{{ $lottery_minute }} {{ $clock->morning == 1 ? 'AM' : 'PM' }}
                                </option>
                            @endforeach
                        </select>
                        @error('clock_id')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>
                </div>

                <input type="hidden" name="lottery_type_id" value="2">

                <div class="settings-form-actions">
                    <a class="btn secondary" href="{{ route('admin.index') }}">Cancel</a>
                    <button class="btn danger" type="submit">
                        <i class="fas fa-bullhorn"></i>
                        Release result
                    </button>
                </div>
            </form>
        </section>
    </div>

    <script>
        $(document).ready(function () {
            $('#input_lottery_number').on('input', function () {
                var number = $(this).val().replace(/\D/g, '').slice(0, 2);
                $(this).val(number);
            });
        });
    </script>
@endsection
