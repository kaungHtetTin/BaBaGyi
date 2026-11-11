<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Thai2DOfficialResult
{
    public function fetch(string $openTime): array
    {
        $results = Http::acceptJson()->connectTimeout(5)->timeout(15)
            ->get('https://api.thaistock2d.com/live')->throw()->json('result');
        $today = CarbonImmutable::now('Asia/Yangon')->toDateString();

        if (is_array($results)) {
            foreach ($results as $result) {
                if (! is_array($result)
                    || ($result['open_time'] ?? null) !== $openTime
                    || ($result['stock_date'] ?? null) !== $today) {
                    continue;
                }
                if (is_string($result['twod'] ?? null)
                    && preg_match('/\A[0-9]{2}\z/', $result['twod'])
                    && ! empty($result['history_id'])) {
                    return $result;
                }
            }
        }

        throw new RuntimeException("No completed Thai 2D result for {$today} {$openTime}; release was skipped.");
    }
}
