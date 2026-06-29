@extends('admin.master')

@section('content')
    <div class="container-fluid">
        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">ACCESS CONTROL</p>
                <h1>Add admin</h1>
            </div>
            <a class="btn secondary" href="{{ route('admin.admins') }}">
                <i class="fas fa-arrow-left"></i>
                Admins
            </a>
        </div>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">TEAM MANAGEMENT</p>
                    <h2>Register new admin</h2>
                    <p class="panel-subtitle">Create an office admin account with dashboard access.</p>
                </div>
            </div>

            <form class="settings-form" action="{{ route('admin.register') }}" method="POST">
                @csrf

                <div class="settings-form-grid">
                    <label class="form-field">
                        <span>Name</span>
                        <input type="text" placeholder="Admin name" name="name" value="{{ old('name') }}">
                        @error('name')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="form-field">
                        <span>Email address</span>
                        <input type="email" placeholder="admin@example.com" name="email" value="{{ old('email') }}">
                        @error('email')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="form-field">
                        <span>Password</span>
                        <input type="password" placeholder="Password" name="password">
                        @error('password')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="form-field">
                        <span>Confirm password</span>
                        <input type="password" placeholder="Confirm password" name="password_confirmation">
                        @error('password_confirmation')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>
                </div>

                <div class="info-block warning">
                    <h3>Admin access</h3>
                    <p>This account will be able to access the office dashboard and operational tools.</p>
                </div>

                <div class="settings-form-actions">
                    <a class="btn secondary" href="{{ route('admin.admins') }}">Cancel</a>
                    <button class="btn primary" type="submit">
                        <i class="fas fa-user-plus"></i>
                        Create admin
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
