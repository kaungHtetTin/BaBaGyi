@extends('admin.master')

@section('content')
    <div class="container-fluid">
        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">RESULT HISTORY</p>
                <h1>Edit MM {{ $type }}D history</h1>
            </div>
            <a class="btn secondary" href="{{ $returnUrl }}">Back</a>
        </div>
        <section class="panel release-panel">
            <div class="panel-heading">
                <div>
                    <h2>{{ $date->format('D, d M Y') }}</h2>
                    <p class="panel-subtitle">Correct saved results or fill a missing result. Myanmar time.</p>
                </div>
            </div>
            <div class="info-block warning">
                <strong>History correction only</strong>
                <p>Voucher win status, payouts, and draw releases are not changed. Leave a field blank to keep its saved result.</p>
            </div>
            <form class="settings-form release-form" method="POST" action="{{ route('admin.lotteries.update-day') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                <input type="hidden" name="source" value="{{ $source }}">
                @if ($returnYear)<input type="hidden" name="year" value="{{ $returnYear }}">@endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">Please check the result fields below.</div>
                @endif
                <div class="settings-form-grid">
                    @foreach ($draws as $time => $draw)
                        <label class="form-field">
                            <span>{{ $draw['label'] }}</span>
                            <input type="text" name="numbers[{{ $time }}]" inputmode="numeric"
                                pattern="[0-9]{{ '{'.$type.'}' }}" maxlength="{{ $type }}"
                                value="{{ old('numbers.'.$time, $draw['number']) }}" placeholder="{{ str_repeat('0', $type) }}"
                                @disabled(! $draw['clock_id'] || ! $draw['due'])
                                @if ($errors->has('numbers.'.$time)) aria-invalid="true" @endif>
                            @if (! $draw['clock_id'])
                                <small class="muted">Draw clock is not configured.</small>
                            @elseif (! $draw['due'])
                                <small class="muted">Available after this draw time.</small>
                            @endif
                            @error('numbers.'.$time)<p class="field-error">{{ $message }}</p>@enderror
                        </label>
                    @endforeach
                </div>
                <div class="settings-form-actions">
                    <a class="btn secondary" href="{{ $returnUrl }}">Cancel</a>
                    <button class="btn primary" type="submit">Save history</button>
                </div>
            </form>
        </section>
    </div>
@endsection
