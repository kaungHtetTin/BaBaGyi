<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class ThreeDWinRules
{
    public function bonusNumbers(string $number): array
    {
        // Preserve the existing adjacent-number calculation.
        $up = (int) $number[2] + 1;
        $upSecond = (int) $number[1];
        if ($up > 9) {
            $up = 0;
            $upSecond++;
        }
        $down = (int) $number[2] - 1;
        $downSecond = (int) $number[1];
        if ($down < 0) {
            $down = 9;
            $downSecond--;
        }
        $bonuses = [$number[0].$upSecond.$up, $number[0].$downSecond.$down];

        foreach ([[0, 1, 2], [0, 2, 1], [1, 0, 2], [1, 2, 0], [2, 0, 1], [2, 1, 0]] as $order) {
            $bonuses[] = $number[$order[0]].$number[$order[1]].$number[$order[2]];
        }

        return array_values(array_filter(array_unique($bonuses), fn ($bonus) => $bonus !== $number));
    }

    public function apply(Builder $vouchers, string $number): void
    {
        $eligible = (clone $vouchers)->where('verified', 0)->where('win', 0);
        (clone $eligible)->where('number', $number)->update(['win' => 1, 'bonus_win' => 0]);
        (clone $eligible)->whereIn('number', $this->bonusNumbers($number))
            ->update(['win' => 1, 'bonus_win' => 1]);
        (clone $eligible)->update(['win' => 0, 'bonus_win' => 0, 'verified' => 1]);
    }
}
