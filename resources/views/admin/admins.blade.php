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
                <p class="eyebrow">ACCESS CONTROL</p>
                <h1>Admins</h1>
            </div>
            @if (Auth::user()->id == 1)
                <a class="btn primary" href="{{ route('admin.register') }}">
                    <i class="fas fa-plus"></i>
                    Add admin
                </a>
            @endif
        </div>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">TEAM MANAGEMENT</p>
                    <h2>Admin accounts</h2>
                    <p class="panel-subtitle">{{ number_format($admins->count()) }} admin accounts</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($admins as $admin)
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <span class="user-avatar">{{ strtoupper(substr($admin->name, 0, 1)) }}</span>
                                        <div>
                                            <strong class="table-primary-line">{{ $admin->name }}</strong>
                                            <small class="table-secondary-line">Admin #{{ $admin->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $admin->email ?: '-' }}</td>
                                <td>{{ $admin->phone ?: '-' }}</td>
                                <td>
                                    @if ($admin->disable == 0)
                                        <span class="status status-success"><span class="status-dot"></span>Active</span>
                                    @else
                                        <span class="status status-danger"><span class="status-dot"></span>Disabled</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="inline-actions dense-actions" aria-label="{{ $admin->name }} actions">
                                        @if ($admin->id != 1)
                                            <a class="icon-btn small danger" href="#" data-toggle="modal" data-target="#disable-modal-{{ $admin->id }}"
                                                aria-label="Disable {{ $admin->name }}" title="Disable">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        @else
                                            <span class="muted">Owner</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"><span class="muted">No admin accounts found.</span></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @foreach ($admins as $admin)
        @if ($admin->id != 1)
            <div class="modal fade" id="disable-modal-{{ $admin->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Disable Admin Account</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">Disable <strong>{{ $admin->name }}</strong> and remove admin access?</p>
                        </div>
                        <div class="modal-footer">
                            <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                            <form action="{{ route('admin.admins.disable', $admin->id) }}" method="POST">
                                @csrf
                                <button class="btn danger">Disable</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection
