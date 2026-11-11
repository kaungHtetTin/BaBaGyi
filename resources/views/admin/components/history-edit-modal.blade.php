@once
    @push('styles')
        <link href="{{ asset('css/admin-history-modal.css') }}?v={{ filemtime(public_path('css/admin-history-modal.css')) }}" rel="stylesheet">
    @endpush
    <div class="modal fade history-edit-modal" id="history-edit-modal" tabindex="-1" role="dialog"
        aria-labelledby="history-edit-title" aria-describedby="history-edit-description" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <p class="eyebrow">RESULT HISTORY</p>
                        <h2 class="modal-title" id="history-edit-title">Edit history</h2>
                        <p class="panel-subtitle mb-0" id="history-edit-date"></p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close history editor"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="history-edit-form" method="POST" action="{{ route('admin.lotteries.update-day') }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <p class="muted" id="history-edit-description">Correct saved results or fill a missing result. Voucher wins, payouts, and releases stay unchanged. Leave a field blank to keep its saved result.</p>
                        <p id="history-edit-loading" role="status">Loading saved results…</p>
                        <div class="alert alert-danger" id="history-edit-error" role="alert" hidden></div>
                        <div class="settings-form-grid" id="history-edit-fields"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn primary" id="history-edit-save" disabled>
                            <i class="fas fa-check" aria-hidden="true"></i> <span>Save history</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="{{ asset('js/admin-history-modal.js') }}?v={{ filemtime(public_path('js/admin-history-modal.js')) }}" defer></script>
    @endpush
@endonce
