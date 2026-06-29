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
                <p class="eyebrow">USER OPERATIONS</p>
                <h1>Users</h1>
            </div>
        </div>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">USER DIRECTORY</p>
                    <h2>Registered users</h2>
                    <p class="panel-subtitle">{{ number_format($users->total()) }} total accounts</p>
                </div>
                <a class="text-btn" href="{{ route('admin.users') }}?sort=balance">Sort by balance</a>
            </div>

            <div class="table-wrap">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Contact</th>
                            <th>Phone</th>
                            <th>Balance</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @if ($user->id != 2)
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <span class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                            <div>
                                                <strong class="table-primary-line">{{ $user->name }}</strong>
                                                <small class="table-secondary-line">User #{{ $user->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="table-primary-line">{{ $user->email ?: '-' }}</strong>
                                        <small class="table-secondary-line">{{ $user->disable ? 'Disabled account' : 'Active account' }}</small>
                                    </td>
                                    <td>{{ $user->phone ?: '-' }}</td>
                                    <td><span class="money-cell">{{ number_format($user->balance) }} MMK</span></td>
                                    <td>
                                        <strong class="table-primary-line">{{ $user->created_at->format('M d, Y') }}</strong>
                                        <small class="table-secondary-line">{{ $user->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="inline-actions dense-actions" aria-label="{{ $user->name }} actions">
                                            <a class="icon-btn small" href="{{ route('admin.users.setting', $user->id) }}"
                                                aria-label="Open user detail" title="User detail">
                                                <i class="fas fa-user-cog"></i>
                                            </a>
                                            <a class="icon-btn small" href="{{ route('admin.users.transactions', $user->id) }}"
                                                aria-label="View top ups" title="Top ups">
                                                <i class="fas fa-coins"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6"><span class="muted">No users found.</span></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </section>
    </div>
@endsection
