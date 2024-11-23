<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "payment_method_id" => rand(1,16),
            "user_id" => rand(2,5),
            "amount" => 20000,
            "verified" => 0,
            "verified_by" => 1,
        ];
    }
}
