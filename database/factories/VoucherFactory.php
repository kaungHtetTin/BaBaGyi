<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id'=>rand(3,100),
            'lottery_type_id'=>2,
            'clock_id'=>rand(1,4),
            'number'=>rand(10,99),
            'amount'=>1000,
            'win'=>0,
            'verified'=>0,
            'verified_by'=>0,
            'created_at'=>"2024-11-".rand(1,30)." 14:43:56",
            'updated_at'=>"2024-11-".rand(1,30)." 14:43:56",
        ];
    }
}
