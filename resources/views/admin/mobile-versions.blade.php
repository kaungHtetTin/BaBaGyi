@extends('admin.master')

@section('content')
    <div class="container-fluid">
        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">APP RELEASES</p>
                <h1>Mobile App Versions</h1>
            </div>
            <a class="btn primary" href="{{ route('admins.mobile-versions.add') }}">
                <i class="fas fa-plus"></i>
                Add version
            </a>
        </div>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">VERSION HISTORY</p>
                    <h2>Android APK releases</h2>
                    <p class="panel-subtitle">{{ number_format($mobile_versions->count()) }} published versions</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Code</th>
                            <th>Minimum Android</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mobile_versions as $version)
                            <tr>
                                <td>
                                    <strong class="table-primary-line">{{ $version->version_name }}</strong>
                                    <small class="table-secondary-line">APK release</small>
                                </td>
                                <td>{{ $version->version_code }}</td>
                                <td>{{ $version->min_android_version }}</td>
                                <td>
                                    <a class="icon-btn small" href="{{ Storage::url('app/public/' . $version->url) }}"
                                        aria-label="Download {{ $version->version_name }}" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"><span class="muted">No mobile versions found.</span></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
