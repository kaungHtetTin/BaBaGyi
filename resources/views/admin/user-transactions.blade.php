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
                <h1>User top ups</h1>
            </div>
            <a class="btn secondary" href="{{ route('admin.users') }}">
                <i class="fas fa-arrow-left"></i>
                Users
            </a>
        </div>

        @include('admin.components.user-detail-header', ['activeTab' => 'transactions'])

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">PAYMENT HISTORY</p>
                    <h2>Top up requests</h2>
                    <p class="panel-subtitle">{{ number_format($transactions->total()) }} total requests</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Banking</th>
                            <th>Account</th>
                            <th>Phone</th>
                            <th>Trx ID</th>
                            <th>Admin</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>
                                    <strong class="table-primary-line">{{ $transaction->created_at->diffForHumans() }}</strong>
                                    <small class="table-secondary-line">{{ $transaction->created_at->format('M d, Y h:i A') }}</small>
                                </td>
                                <td><span class="money-cell">{{ number_format($transaction->amount) }} MMK</span></td>
                                <td>
                                    <span class="bank-cell">
                                        <img src="{{ asset($transaction->payment_method->banking->icon_url) }}" alt="{{ $transaction->payment_method->banking->bank }}">
                                        <strong>{{ $transaction->payment_method->banking->bank }}</strong>
                                    </span>
                                </td>
                                <td>{{ $transaction->payment_method->account_name }}</td>
                                <td>{{ $transaction->payment_method->method }}</td>
                                <td>{{ $transaction->bank_transaction_id }}</td>
                                <td>
                                    @if ($transaction->verified_by != 0)
                                        <strong>{{ $transaction->verified_by($transaction->verified_by)->name }}</strong>
                                    @else
                                        <span class="muted">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($transaction->verified == 1)
                                        <span class="status status-success"><span class="status-dot"></span>Verified</span>
                                    @else
                                        <div class="inline-actions dense-actions">
                                            <a class="icon-btn small success" href="#" data-toggle="modal" data-target="#approve-modal-{{ $transaction->id }}"
                                                aria-label="Approve transaction" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8"><span class="muted">No transaction found.</span></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $transactions->links() }}
        </section>
    </div>

    @foreach ($transactions as $transaction)
        <div class="modal fade" id="approve-modal-{{ $transaction->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Transaction</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Approve <strong>{{ number_format($transaction->amount) }} MMK</strong> top up for <strong>{{ $user->name }}</strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.transactions.approve', $transaction->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button class="btn primary">Approve</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
