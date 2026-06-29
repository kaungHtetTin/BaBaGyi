@extends('admin.master')

@section('content')
    <div class="container-fluid">
        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">MANUAL RELEASE</p>
                <h1>Release 3D result</h1>
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
                    <h2>MM 3D winning number</h2>
                    <p class="panel-subtitle">Enter the confirmed 3D result before releasing it to vouchers.</p>
                </div>
                <span class="status status-danger"><span class="status-dot"></span>Manual mode</span>
            </div>

            <div class="release-warning">
                <span><i class="fas fa-exclamation-triangle"></i></span>
                <div>
                    <strong>Release is final</strong>
                    <p>This will mark winners, apply bonus winners, verify losing vouchers, and reset number demand.</p>
                </div>
            </div>

            <form class="settings-form release-form" action="{{ route('admins.release-3d') }}" method="POST">
                @csrf

                <div class="settings-form-grid compact">
                    <label class="form-field">
                        <span>3D lottery number</span>
                        <input id="input_lottery_number" type="text" name="lottery_number" inputmode="numeric" maxlength="3"
                            placeholder="000" value="{{ old('lottery_number') }}">
                        @error('lottery_number')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>
                </div>

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
                var number = $(this).val().replace(/\D/g, '').slice(0, 3);
                $(this).val(number);
            });
        });
    </script>
@endsection
