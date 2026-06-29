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
                <p class="eyebrow">APP RELEASES</p>
                <h1>Add version update</h1>
            </div>
            <a class="btn secondary" href="{{ route('admins.mobile-versions') }}">
                <i class="fas fa-arrow-left"></i>
                Versions
            </a>
        </div>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">APK RELEASE</p>
                    <h2>New Android version</h2>
                    <p class="panel-subtitle">Upload a new APK and define minimum supported Android version.</p>
                </div>
            </div>

            <form class="settings-form" action="{{ route('admins.mobile-versions.add') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="settings-form-grid">
                    <label class="form-field">
                        <span>Version code</span>
                        <input type="text" placeholder="Version code" name="version_code" value="{{ old('version_code') }}">
                        @error('version_code')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="form-field">
                        <span>Version name</span>
                        <input type="text" placeholder="Version name" name="version_name" value="{{ old('version_name') }}">
                        @error('version_name')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="form-field">
                        <span>Minimum Android version</span>
                        <input type="text" placeholder="Minimum Android version" name="min_android_version" value="{{ old('min_android_version') }}">
                        @error('min_android_version')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>

                    <label class="form-field">
                        <span>APK file</span>
                        <input type="file" name="anroid_apk_file" accept=".apk">
                        @error('anroid_apk_file')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </label>
                </div>

                <div class="settings-form-actions">
                    <a class="btn secondary" href="{{ route('admins.mobile-versions') }}">Cancel</a>
                    <button class="btn primary" type="submit">
                        <i class="fas fa-upload"></i>
                        Publish version
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
