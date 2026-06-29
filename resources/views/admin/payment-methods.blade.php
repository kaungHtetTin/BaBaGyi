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
                <p class="eyebrow">PAYMENT CONFIGURATION</p>
                <h1>Payment Methods</h1>
            </div>
            <a class="btn primary" href="{{ route('admin.payment-methods.create') }}">
                <i class="fas fa-plus"></i>
                Add method
            </a>
        </div>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">CASH-IN ACCOUNTS</p>
                    <h2>Available payment methods</h2>
                    <p class="panel-subtitle">{{ number_format($payment_methods->total()) }} configured methods</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Banking</th>
                            <th>Account</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payment_methods as $method)
                            <tr>
                                <td>
                                    <span class="bank-cell">
                                        <img src="{{ asset($method->banking->icon_url) }}" alt="{{ $method->banking->bank }}">
                                        <strong>{{ $method->banking->bank }}</strong>
                                    </span>
                                </td>
                                <td>
                                    <strong class="table-primary-line">{{ $method->account_name }}</strong>
                                    <small class="table-secondary-line">Payment account</small>
                                </td>
                                <td>{{ $method->method }}</td>
                                <td>
                                    @if ($method->disable == 0)
                                        <span class="status status-success"><span class="status-dot"></span>Active</span>
                                    @else
                                        <span class="status status-danger"><span class="status-dot"></span>Disabled</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="inline-actions dense-actions" aria-label="{{ $method->account_name }} actions">
                                        @if ($method->disable == 0)
                                            <a class="icon-btn small danger" href="#" data-toggle="modal" data-target="#disable-modal-{{ $method->id }}"
                                                aria-label="Disable payment method" title="Disable">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        @else
                                            <a class="icon-btn small success" href="#" data-toggle="modal" data-target="#activate-modal-{{ $method->id }}"
                                                aria-label="Activate payment method" title="Activate">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"><span class="muted">No payment methods found.</span></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $payment_methods->links() }}
        </section>
    </div>

    @foreach ($payment_methods as $method)
        <div class="modal fade" id="disable-modal-{{ $method->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Disable Payment Method</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Hide <strong>{{ $method->account_name }}</strong> from users?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.payment-methods.disable') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $method->id }}">
                            <button class="btn danger">Disable</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="activate-modal-{{ $method->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Activate Payment Method</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Make <strong>{{ $method->account_name }}</strong> available to users?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.payment-methods.enable') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $method->id }}">
                            <button class="btn primary">Activate</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
