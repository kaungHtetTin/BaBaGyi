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
                <p class="eyebrow">ACCOUNT SETTINGS</p>
                <h1>Profile</h1>
            </div>
        </div>

        <section class="admin-grid">
            <article class="panel glass user-profile-panel">
                <div class="user-profile-heading">
                    <div class="user-identity">
                        @if (!empty($user->avatar_url))
                            <img class="profile-avatar-large" src="{{ asset($user->avatar_url) }}" alt="{{ $user->name }}">
                        @else
                            <span class="user-avatar large">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        @endif
                        <div>
                            <p class="eyebrow">ADMIN PROFILE</p>
                            <h2>{{ $user->name }}</h2>
                            <p>{{ $user->email ?: 'No email recorded' }}</p>
                        </div>
                    </div>
                    <span class="status status-success"><span class="status-dot"></span>Signed in</span>
                </div>

                <div class="user-detail-grid">
                    <div>
                        <span>Phone</span>
                        <strong>{{ $user->phone ?: '-' }}</strong>
                    </div>
                    <div>
                        <span>Role</span>
                        <strong>{{ $user->admin ? 'Office admin' : 'User' }}</strong>
                    </div>
                    <div>
                        <span>Joined</span>
                        <strong>{{ $user->created_at ? $user->created_at->format('M d, Y') : '-' }}</strong>
                    </div>
                    <div>
                        <span>Status</span>
                        <strong>{{ $user->disable ? 'Disabled' : 'Active' }}</strong>
                    </div>
                </div>

                <div class="profile-section">
                    <div class="panel-heading compact-heading">
                        <div>
                            <p class="eyebrow">AVATAR</p>
                            <h2>Select profile image</h2>
                            <p class="panel-subtitle">Choose the avatar shown in the admin sidebar.</p>
                        </div>
                    </div>

                    <div class="avatar-picker-grid">
                        @foreach ($avatars as $avatar)
                            <form action="{{ route('admin.avatar.modify') }}" method="POST">
                                @csrf
                                <input type="hidden" name="avatar_url" value="{{ $avatar->url }}">
                                <button class="avatar-choice {{ $user->avatar_url === $avatar->url ? 'active' : '' }}" type="submit"
                                    aria-label="Use this avatar">
                                    <img src="{{ asset($avatar->url) }}" alt="">
                                    @if ($user->avatar_url === $avatar->url)
                                        <span><i class="fas fa-check"></i></span>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </article>

            <aside class="panel glass">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">SECURITY</p>
                        <h2>Change password</h2>
                        <p class="panel-subtitle">Update the password used for this admin account.</p>
                    </div>
                </div>

                <form class="settings-form" action="{{ route('password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <label class="form-field">
                        <span>Current password</span>
                        <input type="password" placeholder="Enter current password" name="current_password">
                        @if ($errors->updatePassword->first('current_password'))
                            <p class="field-error">{{ $errors->updatePassword->first('current_password') }}</p>
                        @endif
                    </label>

                    <label class="form-field">
                        <span>New password</span>
                        <input type="password" placeholder="Enter new password" name="password">
                        @if ($errors->updatePassword->first('password'))
                            <p class="field-error">{{ $errors->updatePassword->first('password') }}</p>
                        @endif
                    </label>

                    <div class="settings-form-actions">
                        <button class="btn primary" type="submit">
                            <i class="fas fa-key"></i>
                            Update password
                        </button>
                    </div>
                </form>
            </aside>
        </section>
    </div>
@endsection
