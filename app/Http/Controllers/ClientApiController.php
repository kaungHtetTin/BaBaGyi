<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;

class ClientApiController extends Controller
{
    // A same-origin bridge keeps the native API's personal token in the server session.
    // No access token or account data is included in the cacheable app shell.
    public function session(Request $request)
    {
        return response()->json(['csrf' => csrf_token(), 'authenticated' => (bool) ($request->session()->get('client_token') ?: $request->cookie('mastergyi_client'))])
            ->header('Cache-Control', 'no-store');
    }

    public function handle(Request $request, string $endpoint)
    {
        $routes = [
            'login' => ['POST', Api\AuthController::class, 'login', false],
            'register' => ['POST', Api\AuthController::class, 'register', false],
            'check-user' => ['POST', Api\AuthController::class, 'checkUser', false],
            'user' => ['GET', Api\UserController::class, 'user', true],
            'user/update' => ['POST', Api\UserController::class, 'update', true],
            'user/update-password-recovery-hint' => ['POST', Api\UserController::class, 'updateRecoveryHint', true],
            'user/vouchers' => ['GET', Api\UserController::class, 'vouchers', true],
            'user/wallet-histories' => ['GET', Api\UserController::class, 'walletHistories', true],
            'password-reset' => ['POST', Api\AuthController::class, 'resetPassword', true],
            'delete-account' => ['POST', Api\AuthController::class, 'deleteAccount', true],
            'top-up' => ['POST', Api\TransactionController::class, 'store', true],
            'withdraw' => ['POST', Api\WithdrawController::class, 'store', true],
            'vouchers/store' => ['POST', Api\VoucherController::class, 'store', true],
            'notices' => ['GET', Api\NoticeController::class, 'index', true],
            'payment-method' => ['GET', Api\PaymentMethodController::class, 'payment_method', true],
            'bankings' => ['GET', Api\BankingController::class, 'index', false],
            'avatars' => ['GET', Api\AvatarController::class, 'index', false],
            'contacts' => ['GET', Api\ContactController::class, 'index', false],
            'numbers' => ['GET', Api\NumberController::class, 'index', false],
            'lotteries/records' => ['GET', Api\LotteryController::class, 'records', false],
            'lotteries/calendar' => ['GET', Api\LotteryController::class, 'calendar', false],
            'lotteries/3d-records' => ['GET', Api\LotteryController::class, '_3d_records', false],
            'remote/thai-2d' => ['GET', Api\RemoteNumberController::class, 'get2DNumber', false],
            'remote/thai-3d' => ['GET', Api\RemoteNumberController::class, 'get3DNumber', false],
            'latest-version' => ['GET', Api\MobileVersionController::class, 'get_latest_version', false],
        ];
        $request->headers->set('Accept', 'application/json');
        $token = $request->session()->get('client_token') ?: $request->cookie('mastergyi_client');
        $access = $token ? PersonalAccessToken::findToken($token) : null;
        $user = $access?->tokenable;
        $request->setUserResolver(fn () => $user);

        if ($endpoint === 'logout' && $request->isMethod('POST')) {
            $access?->delete();
            $request->session()->forget('client_token');
            cookie()->queue(cookie()->forget('mastergyi_client', parse_url(url('/app'), PHP_URL_PATH)));
            return response()->json(['status' => 'success'])->header('Cache-Control', 'no-store');
        }
        if (preg_match('~^lottery-types/([23])/clocks$~', $endpoint, $match)) {
            abort_unless($request->isMethod('GET'), 405);
            return response()->json(app(Api\LotteryTypeController::class)->clocks((int) $match[1]))->header('Cache-Control', 'no-store');
        }
        abort_unless(isset($routes[$endpoint]), 404);
        [$method, $controller, $action, $authenticated] = $routes[$endpoint];
        abort_unless($request->isMethod($method), 405);
        if ($authenticated && ! $user) return response()->json(['message' => 'Unauthenticated.'], 401);
        if ($authenticated && $user->disable && $endpoint !== 'user') return response()->json(['message' => 'Account disabled.', 'disabled' => true], 403);

        if (in_array($endpoint, ['login', 'register', 'check-user'], true)) {
            $key = 'client-auth:'.sha1($request->ip());
            if (RateLimiter::tooManyAttempts($key, 20)) return response()->json(['message' => 'Too many attempts. Please try again later.'], 429);
            RateLimiter::hit($key, 60);
        }

        $invoke = function () use ($controller, $action, $request) {
            $result = app($controller)->$action($request);
            if ($result instanceof \Illuminate\Http\JsonResponse || $result instanceof \Illuminate\Http\Response) return $result;
            return response()->json(is_string($result) ? json_decode($result, true, 512, JSON_THROW_ON_ERROR) : $result);
        };
        $financial = in_array($endpoint, ['top-up', 'withdraw', 'vouchers/store'], true);
        if ($financial) {
            $request->validate(['request_id' => 'required|uuid']);
            $fingerprint = hash('sha256', $endpoint.json_encode($request->except(['_token', 'request_id'])));
            $response = DB::transaction(function () use ($invoke, $fingerprint, $user, $request, $endpoint) {
                    $lockedUser = \App\Models\User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                    $previous = DB::table('client_requests')->where('user_id', $user->id)->where('request_id', $request->request_id)->first();
                    if ($previous) {
                        abort_unless(hash_equals($previous->fingerprint, $fingerprint), 409, 'This request ID was already used.');
                        return response()->json(json_decode($previous->body, true), $previous->status);
                    }
                    $this->validateMutation($request, $endpoint);
                    $request->setUserResolver(fn () => $lockedUser);
                    if ($endpoint === 'vouchers/store') {
                        $rows = json_decode($request->numberJSON, true);
                        \App\Models\Number::whereIn('id', array_column($rows, 'id'))->orderBy('id')->lockForUpdate()->get();
                    }
                    $response = $invoke();
                    if ($response->getStatusCode() < 400) DB::table('client_requests')->insert([
                        'user_id' => $user->id, 'request_id' => $request->request_id, 'fingerprint' => $fingerprint,
                        'body' => $response->getContent(), 'status' => $response->getStatusCode(), 'created_at' => now(),
                    ]);
                    return $response;
            });
        } else {
            $response = $invoke();
        }
        if (in_array($endpoint, ['login', 'register'], true)) {
            $data = $response->getData(true);
            if (isset($data['_token'])) {
                $request->session()->regenerate();
                $request->session()->put('client_token', $data['_token']);
                // Laravel encrypts this scoped, HTTP-only cookie. It preserves native-style sign-in.
                cookie()->queue(cookie('mastergyi_client', $data['_token'], 60 * 24 * 90,
                    parse_url(url('/app'), PHP_URL_PATH), null, $request->isSecure(), true, false, 'lax'));
                unset($data['_token']);
                $response->setData($data);
            }
        }
        if ($endpoint === 'delete-account' && ($response->getData(true)['status'] ?? '') === 'success') {
            $access?->delete();
            $request->session()->forget('client_token');
            cookie()->queue(cookie()->forget('mastergyi_client', parse_url(url('/app'), PHP_URL_PATH)));
        }
        return $response->header('Cache-Control', 'no-store');
    }

    private function validateMutation(Request $request, string $endpoint): void
    {
        if ($endpoint === 'top-up') {
            $request->validate(['payment_method_id' => 'required|integer|exists:payment_methods,id',
                'amount' => 'required|integer|min:100', 'bank_transaction_id' => 'required|digits:6']);
        } elseif ($endpoint === 'withdraw') {
            $request->validate(['banking_id' => 'required|integer|exists:bankings,id',
                'amount' => 'required|integer|min:1', 'method' => 'required|string|max:100', 'account_name' => 'required|string|max:255']);
        } else {
            $request->validate(['lottery_type_id' => 'required|integer|in:2,3', 'clock_id' => 'required|integer', 'numberJSON' => 'required|json']);
            $rows = json_decode($request->numberJSON, true);
            validator(['rows' => $rows], ['rows' => 'required|array|min:1|max:1000', 'rows.*.id' => 'required|integer',
                'rows.*.number' => 'required|regex:/^[0-9]{2,3}$/', 'rows.*.amount' => 'required|integer|min:100'])->validate();
            $clock = \App\Models\LotteryClock::with('clock')->where('lottery_type_id', $request->lottery_type_id)->where('clock_id', $request->clock_id)->first();
            $type = \App\Models\LotteryType::find($request->lottery_type_id);
            abort_unless($clock && $type && $type->open, 422, 'Lottery closed.');
            $now = now('Asia/Yangon');
            $hour = $clock->clock->hour % 12 + ($clock->clock->morning ? 0 : 12);
            $draw = $now->copy()->setTime($hour, $clock->clock->minute);
            if ((int) $request->lottery_type_id === 2) {
                abort_if($now->isWeekend() || $now->greaterThanOrEqualTo($draw->subMinutes($clock->close_before)), 422, 'Lottery closed.');
            } elseif (in_array($now->day, [1, 16], true)) {
                abort_if($now->greaterThanOrEqualTo($draw->subMinutes($clock->close_before)), 422, 'Lottery closed.');
            }
            $numbers = \App\Models\Number::where('lottery_type_id', $request->lottery_type_id)->where('clock_id', $request->clock_id)->whereIn('id', array_column($rows, 'id'))->get()->keyBy('id');
            foreach ($rows as $row) {
                abort_unless(isset($numbers[$row['id']]) && (string) $numbers[$row['id']]->number === (string) $row['number'], 422, 'Invalid number selection.');
            }
        }
    }
}
