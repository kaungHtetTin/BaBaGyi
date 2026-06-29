@extends('admin.master')

@section('content')
    <div class="container-fluid">
        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">USER OPERATIONS</p>
                <h1>User wallet history</h1>
            </div>
            <a class="btn secondary" href="{{ route('admin.users') }}">
                <i class="fas fa-arrow-left"></i>
                Users
            </a>
        </div>

        @include('admin.components.user-detail-header', ['activeTab' => 'wallet'])

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">WALLET LEDGER</p>
                    <h2>Wallet history</h2>
                    <p class="panel-subtitle">{{ number_format($wallet_histories->total()) }} total records</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($wallet_histories as $history)
                            <tr>
                                <td>
                                    <strong class="table-primary-line">{{ $history->created_at->diffForHumans() }}</strong>
                                    <small class="table-secondary-line">{{ $history->created_at->format('M d, Y h:i A') }}</small>
                                </td>
                                <td><strong>{{ $history->title }}</strong></td>
                                <td>
                                    @if ($history->income == 1)
                                        <span class="status status-success"><span class="status-dot"></span>Income</span>
                                    @else
                                        <span class="status status-danger"><span class="status-dot"></span>Expense</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="money-cell">{{ $history->income == 1 ? '+' : '-' }}{{ number_format($history->amount) }} MMK</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"><span class="muted">No wallet history found.</span></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $wallet_histories->links() }}
        </section>
    </div>
@endsection
