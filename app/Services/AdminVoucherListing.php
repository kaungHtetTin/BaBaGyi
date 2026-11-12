<?php

namespace App\Services;

use App\Models\Clock;
use App\Models\LotteryClock;
use App\Models\LotteryType;
use App\Models\User;
use App\Models\Voucher;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminVoucherListing
{
    public const STATUSES = [
        'waiting' => 'Waiting for result',
        'unpaid_win' => 'Unpaid winner',
        'paid' => 'Paid winner',
        'failed' => 'Failed',
        'bonus_failed' => 'Bonus fail',
    ];

    public function data(Request $request, int $type, string $title): array
    {
        $clocks = Clock::where(function ($query) use ($type) {
            $query->whereIn('id', LotteryClock::where('lottery_type_id', $type)->select('clock_id'))
                ->orWhereIn('id', Voucher::where('lottery_type_id', $type)->select('clock_id'));
        })->when($type === 2, fn ($query) => $query->whereNotIn('id', LotteryHistory::hiddenClockIds()))
            ->get()->sortBy(fn ($clock) => ($clock->hour % 12 + ($clock->morning ? 0 : 12)) * 60 + $clock->minute)->values();
        $admins = User::where('admin', 1)->orderBy('name')->get(['id', 'name']);
        $statuses = self::STATUSES;
        if ($type !== 3) {
            unset($statuses['bonus_failed']);
        }
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'number' => ['nullable', 'string', 'regex:/\A[0-9]{'.($type === 3 ? 3 : 2).'}\z/'],
            'clock_id' => ['nullable', 'integer', Rule::in($clocks->pluck('id')->all())],
            'status' => ['nullable', Rule::in(array_keys($statuses))],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => array_merge(['nullable', 'date_format:Y-m-d'], $request->filled('date_from') ? ['after_or_equal:date_from'] : []),
            'admin_id' => ['nullable', Rule::in(array_merge(['unassigned'], $admins->pluck('id')->all()))],
            'amount_min' => ['nullable', 'numeric', 'min:0'],
            'amount_max' => array_merge(['nullable', 'numeric', 'min:0'], $request->filled('amount_min') ? ['gte:amount_min'] : []),
            'sort' => ['nullable', Rule::in(['queue', 'newest', 'oldest', 'amount_desc', 'amount_asc'])],
            'per_page' => ['nullable', 'integer', Rule::in([25, 50, 100])],
        ]);
        $filters['q'] = trim($filters['q'] ?? '');
        $filters['sort'] = $filters['sort'] ?? 'queue';
        $filters['per_page'] = (int) ($filters['per_page'] ?? 50);

        $query = Voucher::where('lottery_type_id', $type)->where('user_id', '!=', 2)
            ->when($type === 2, fn ($query) => $query->whereNotIn('clock_id', LotteryHistory::hiddenClockIds()));
        if ($filters['q'] !== '') {
            $search = $filters['q'];
            $query->where(function ($query) use ($search) {
                $query->whereHas('user', function ($user) use ($search) {
                    $user->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
                if (ctype_digit($search)) {
                    $query->orWhere('id', $search);
                }
            });
        }
        foreach (['number', 'clock_id'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }
        if (isset($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }
        if (isset($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', CarbonImmutable::parse($filters['date_from'], 'Asia/Yangon')->startOfDay());
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<', CarbonImmutable::parse($filters['date_to'], 'Asia/Yangon')->addDay()->startOfDay());
        }
        if (! empty($filters['admin_id'])) {
            if ($filters['admin_id'] === 'unassigned') {
                $query->where(fn ($query) => $query->whereNull('verified_by')->orWhere('verified_by', 0));
            } else {
                $query->where('verified_by', $filters['admin_id']);
            }
        }
        switch ($filters['status'] ?? '') {
            case 'waiting':
                $query->where('win', 0)->where('verified', 0);
                break;
            case 'unpaid_win':
                $query->where('win', 1)->where('verified', 0)
                    ->where(fn ($query) => $query->whereNull('verified_by')->orWhere('verified_by', 0));
                break;
            case 'paid':
                $query->where('win', 1)->where('verified', 1);
                break;
            case 'failed':
                $query->where('win', 0)->where('verified', 1)->where('bonus_win', 0);
                break;
            case 'bonus_failed':
                $query->where('win', 0)->where('verified', 1)->where('bonus_win', 1);
                break;
        }

        $lotteryType = LotteryType::findOrFail($type);
        $coefficient = (float) $lotteryType->coefficient;
        $bonusCoefficient = $type === 3 ? (float) $lotteryType->bonus_coefficient : $coefficient;
        $summary = (clone $query)->selectRaw('COALESCE(SUM(amount), 0) AS sales,
            COALESCE(SUM(CASE WHEN win = 1 THEN CASE WHEN win_amount > 0 THEN win_amount ELSE amount * CASE WHEN bonus_win = 1 THEN ? ELSE ? END END ELSE 0 END), 0) AS payout,
            COALESCE(SUM(CASE WHEN verified = 0 THEN 1 ELSE 0 END), 0) AS pending,
            COALESCE(SUM(CASE WHEN win = 1 THEN 1 ELSE 0 END), 0) AS winners', [$bonusCoefficient, $coefficient])->first();

        switch ($filters['sort']) {
            case 'newest':
                $query->orderByDesc('created_at');
                break;
            case 'oldest':
                $query->orderBy('created_at');
                break;
            case 'amount_desc':
                $query->orderByDesc('amount');
                break;
            case 'amount_asc':
                $query->orderBy('amount');
                break;
            default:
                $query->orderBy('verified')->orderByDesc('created_at');
                if ($type === 3) {
                    $query->orderByDesc('bonus_win');
                }
                $query->orderBy('number');
        }
        $vouchers = $query->orderByDesc('id')->with(['user:id,name,email,phone,balance',
            'clock:id,hour,minute,morning', 'lottery_type:id,coefficient,bonus_coefficient', 'reviewer:id,name'])
            ->paginate($filters['per_page'])->withQueryString();

        return compact('vouchers', 'filters', 'clocks', 'admins', 'statuses', 'summary', 'title', 'type')
            + ['page_name' => 'Vouchers'];
    }
}
