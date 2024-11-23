<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Withdraw>
 */
class WithdrawFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => rand(2,4),
            'banking_id' => rand(1,4),
            'method'=>'09516123547',
            'account_name'=>'Mg Bg',
            'amount'=> 500,
            'verified'=>0,
            'verified_by' =>0,
        ];
    }
}
