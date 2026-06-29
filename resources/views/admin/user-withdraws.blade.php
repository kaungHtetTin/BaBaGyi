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
                <h1>User withdraws</h1>
            </div>
            <a class="btn secondary" href="{{ route('admin.users') }}">
                <i class="fas fa-arrow-left"></i>
                Users
            </a>
        </div>

        @include('admin.components.user-detail-header', ['activeTab' => 'withdraws'])

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">PAYOUT HISTORY</p>
                    <h2>Withdraw requests</h2>
                    <p class="panel-subtitle">{{ number_format($withdraws->total()) }} total requests</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Balance</th>
                            <th>Withdraw</th>
                            <th>Banking</th>
                            <th>Account</th>
                            <th>Phone</th>
                            <th>Admin</th>
                            <th>Approved</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($withdraws as $withdraw)
                            <tr>
                                <td>
                                    <strong class="table-primary-line">{{ $withdraw->created_at->diffForHumans() }}</strong>
                                    <small class="table-secondary-line">{{ $withdraw->created_at->format('M d, Y h:i A') }}</small>
                                </td>
                                <td><span class="money-cell">{{ number_format($withdraw->user->balance) }} MMK</span></td>
                                <td><span class="money-cell">{{ number_format($withdraw->amount) }} MMK</span></td>
                                <td>
                                    <span class="bank-cell">
                                        <img src="{{ asset($withdraw->banking->icon_url) }}" alt="{{ $withdraw->banking->bank }}">
                                        <strong>{{ $withdraw->banking->bank }}</strong>
                                    </span>
                                </td>
                                <td>{{ $withdraw->account_name }}</td>
                                <td>{{ $withdraw->method }}</td>
                                <td>
                                    @if ($withdraw->verified_by != 0)
                                        <strong>{{ $withdraw->verified_by($withdraw->verified_by)->name }}</strong>
                                    @else
                                        <span class="muted">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($withdraw->verified_by != 0)
                                        <strong class="table-primary-line">{{ $withdraw->updated_at->diffForHumans() }}</strong>
                                        <small class="table-secondary-line">{{ $withdraw->updated_at->format('M d, Y h:i A') }}</small>
                                    @else
                                        <span class="muted">Not sent</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($withdraw->verified == 1)
                                        <span class="status status-success"><span class="status-dot"></span>Sent</span>
                                    @else
                                        <div class="inline-actions dense-actions">
                                            <a class="icon-btn small success" href="#" data-toggle="modal" data-target="#approve-modal-{{ $withdraw->id }}"
                                                aria-label="Mark withdraw sent" title="Mark sent">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9"><span class="muted">No withdraw request found.</span></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $withdraws->links() }}
        </section>
    </div>

    @foreach ($withdraws as $withdraw)
        <div class="modal fade" id="approve-modal-{{ $withdraw->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mark Withdraw Sent</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Mark <strong>{{ number_format($withdraw->amount) }} MMK</strong> withdraw for <strong>{{ $user->name }}</strong> as sent?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.withdraws.approve', $withdraw->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button class="btn primary">Mark sent</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
