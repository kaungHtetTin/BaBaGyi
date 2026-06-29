@php
    $transactionRows = $transactions->getCollection();
    $pendingCount = $transactionRows->where('verified', 0)->count();
    $verifiedCount = $transactionRows->where('verified', 1)->count();
@endphp

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
                <p class="eyebrow">FINANCIAL REVIEW</p>
                <h1>Top Up</h1>
            </div>
        </div>

        <section class="report-metrics" aria-label="Top up metrics">
            <article class="metric-card glass">
                <span><i class="fas fa-coins"></i></span>
                <small>Amount today</small>
                <strong>{{ number_format($amount_today) }}</strong>
                <p>MMK received today</p>
            </article>
            <article class="metric-card glass">
                <span><i class="fas fa-clock"></i></span>
                <small>Pending review</small>
                <strong>{{ number_format($pendingCount) }}</strong>
                <p>On this page</p>
            </article>
            <article class="metric-card glass">
                <span><i class="fas fa-check"></i></span>
                <small>Verified</small>
                <strong>{{ number_format($verifiedCount) }}</strong>
                <p>On this page</p>
            </article>
        </section>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">PAYMENT QUEUE</p>
                    <h2>Top up requests</h2>
                    <p class="panel-subtitle">{{ number_format($transactions->total()) }} total requests</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User</th>
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
                                <td>
                                    <a class="table-primary-line" href="{{ route('admin.users.transactions', $transaction->user_id) }}">{{ $transaction->user->name }}</a>
                                    <small class="table-secondary-line">{{ $transaction->user->phone ?? 'No phone' }}</small>
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
                                        <div class="inline-actions">
                                            <a class="btn primary" href="#" data-toggle="modal" data-target="#approve-modal-{{ $transaction->id }}">Approve</a>
                                            <a class="btn danger" href="#" data-toggle="modal" data-target="#delete-modal-{{ $transaction->id }}">Delete</a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9"><span class="muted">No top up requests found.</span></td>
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
                        <p class="mb-0">Approve <strong>{{ number_format($transaction->amount) }} MMK</strong> for <strong>{{ $transaction->user->name }}</strong>?</p>
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

        <div class="modal fade" id="delete-modal-{{ $transaction->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Transaction</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0"><strong>Warning:</strong> This action cannot be undone. Delete this transaction only if the submitted payment is wrong.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.transactions.remove', $transaction->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
