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
                <p class="eyebrow">PAYMENT CONFIGURATION</p>
                <h1>Add payment method</h1>
            </div>
            <a class="btn secondary" href="{{ route('admin.payment-methods') }}">
                <i class="fas fa-arrow-left"></i>
                Payment methods
            </a>
        </div>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">CASH-IN ACCOUNT</p>
                    <h2>New payment method</h2>
                    <p class="panel-subtitle">Create the same account across one or more banking channels.</p>
                </div>
            </div>

            <form class="settings-form" action="{{ route('admin.payment-methods.store') }}" method="POST">
                @csrf

                <div class="settings-form-grid">
                    <label class="form-field">
                        <span>Mobile banking phone</span>
                        <input type="text" placeholder="09..." name="phone" value="{{ old('phone') }}">
                        @error('phone')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="form-field">
                        <span>Account name</span>
                        <input type="text" placeholder="Account owner name" name="account_name" value="{{ old('account_name') }}">
                        @error('account_name')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>
                </div>

                <div class="crud-option-panel">
                    <p class="eyebrow">BANKING CHANNELS</p>
                    <div class="checkbox-list">
                        @foreach ($bankings as $banking)
                            <label class="check-option">
                                <input type="checkbox" name="banking_{{ $banking->id }}" {{ old('banking_' . $banking->id) ? 'checked' : '' }}>
                                <span>{{ $banking->bank }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if (session('banking_error'))
                        <p class="field-error">{{ session('banking_error') }}</p>
                    @endif
                </div>

                <div class="settings-form-actions">
                    <a class="btn secondary" href="{{ route('admin.payment-methods') }}">Cancel</a>
                    <button class="btn primary" type="submit">
                        <i class="fas fa-save"></i>
                        Add method
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
