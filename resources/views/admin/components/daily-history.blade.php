<section class="panel" aria-labelledby="daily-history-heading">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">RESULT HISTORY</p>
            <h2 id="daily-history-heading">Saved results · {{ $historyDate->format('d M Y') }}</h2>
        </div>
        <form class="d-flex align-items-end flex-wrap" style="gap: 8px" method="GET" action="{{ route('admin.index') }}">
            @if (request('year'))<input type="hidden" name="year" value="{{ request('year') }}">@endif
            <label class="form-field mb-0">
                <span>Result date</span>
                <input type="date" name="date" value="{{ $historyDate->toDateString() }}"
                    max="{{ \Carbon\CarbonImmutable::now('Asia/Yangon')->toDateString() }}" required>
            </label>
            <button class="btn secondary" type="submit">Show day</button>
        </form>
    </div>
    <div class="admin-grid">
        @foreach ($dailyHistory as $historyType => $draws)
            <div class="info-block">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h3 class="mb-0">MM {{ $historyType }}D</h3>
                    <button type="button" class="icon-btn small" data-toggle="modal" data-target="#history-edit-modal"
                        data-history-url="{{ route('admin.lotteries.edit-day', array_filter([
                        'type' => $historyType, 'date' => $historyDate->toDateString(),
                        'source' => 'dashboard', 'year' => request('year'),
                    ])) }}" aria-label="Edit MM {{ $historyType }}D history" title="Edit history">
                        <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                    </button>
                </div>
                <table class="mini-table" aria-label="Saved MM {{ $historyType }}D results">
                    @foreach ($draws as $draw)
                        <tr><th scope="row">{{ $draw['label'] }}</th><td><strong>{{ $draw['number'] ?? '--' }}</strong></td></tr>
                    @endforeach
                </table>
            </div>
        @endforeach
    </div>
</section>
@include('admin.components.history-edit-modal')
