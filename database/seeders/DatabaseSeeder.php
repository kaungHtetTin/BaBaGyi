<?php

namespace Database\Seeders;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Lottery;
use App\Models\Transaction;
use App\Models\Withdraw;
use App\Models\Voucher;
use App\Models\Clock;
use App\Models\LotteryType;
use App\Models\LotteryClock;
use App\Models\Avatar;
use App\Models\Banking;
use App\Models\Number;

use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        // User::create([
        //     'name' => 'Super Admin',
        //     'email' => 'super@babagyi.com',
        //     'email_verified_at' => now(),
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'remember_token' => Str::random(10),
        //     'admin'=>1,
        //     'phone' => '00',
        // ]);

        // User::create([
        //     'name' => 'Disable User',
        //     'email' => 'disable@babagyi.com',
        //     'email_verified_at' => now(),
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'remember_token' => Str::random(10),
        //     'phone' => '00',
        // ]);

        // Clock::create([
        //     'hour'=>9,
        //     'minute'=>30,
        //     'second'=>0,
        //     'morning'=>1
        // ]);

        // Clock::create([
        //     'hour'=>12,
        //     'minute'=>1,
        //     'second'=>0,
        //     'morning'=>0
        // ]);

        // Clock::create([
        //     'hour'=>2,
        //     'minute'=>0,
        //     'second'=>0,
        //     'morning'=>0
        // ]);

        // Clock::create([
        //     'hour'=>4,
        //     'minute'=>30,
        //     'second'=>0,
        //     'morning'=>0
        // ]);

        // Clock::create([
        //     'hour'=>3,
        //     'minute'=>30,
        //     'second'=>0,
        //     'morning'=>0
        // ]);

        // LotteryType::create([
        //     'type'=>'BTC 2D',
        //     'coefficient'=>80,
        //     'api_url'=>"",
        // ]);

        // LotteryType::create([
        //     'type'=>'Thai 2D',
        //     'coefficient'=>80,
        //     'api_url'=>"",
        // ]);

        // LotteryType::create([
        //     'type'=>'Thai 3D',
        //     'coefficient'=>500,
        //     'api_url'=>"",
        // ]);

        // Avatar::create(['url'=>'img/undraw_profile.png']);
        // Avatar::create(['url'=>'img/undraw_profile_1.png']);
        // Avatar::create(['url'=>'img/undraw_profile_2.png']);
        // Avatar::create(['url'=>'img/undraw_profile_3.png']);

        // LotteryClock::create(['lottery_type_id'=>1,'clock_id'=>1]);
        // LotteryClock::create(['lottery_type_id'=>1,'clock_id'=>2]);
        // LotteryClock::create(['lottery_type_id'=>1,'clock_id'=>3]);
        // LotteryClock::create(['lottery_type_id'=>1,'clock_id'=>4]);
        // LotteryClock::create(['lottery_type_id'=>2,'clock_id'=>2]);
        // LotteryClock::create(['lottery_type_id'=>2,'clock_id'=>4]);
        // LotteryClock::create(['lottery_type_id'=>3,'clock_id'=>5]);

        // Banking::create(['bank'=>'KBZ pay','icon_url'=>'img/payment-kbz-pay.jpg']);
        // Banking::create(['bank'=>'Wave pay','icon_url'=>'img/payment-wave-pay.jpg']);
        // Banking::create(['bank'=>'CB pay','icon_url'=>'img/payment-cb-pay.png']);
        // Banking::create(['bank'=>'AYA pay','icon_url'=>'img/payment-aya-pay.png']);

        // for($i=0; $i<=9 ; $i++){
        //     for($j=0; $j<=9; $j++){
        //         $number = $i.$j;
        //         Number::create([
        //             'clock_id'=>2,
        //             'lottery_type_id'=>2,
        //             'number' => $number,
        //             'sell'=> 1000000,
        //             'disable'=>0,
        //             'demand'=>0,
        //         ]);

        //     }
        // }

        // for($i=0; $i<=9 ; $i++){
        //     for($j=0; $j<=9; $j++){
        //         $number = $i.$j;
        //         Number::create([
        //             'clock_id'=>4,
        //             'lottery_type_id'=>2,
        //             'number' => $number,
        //             'sell'=> 1000000,
        //             'demand'=>0,
        //             'disable'=>0,
        //         ]);
        //     }
        // }

        // for($i=0; $i<=9 ; $i++){
        //     for($j=0; $j<=9; $j++){
        //         for($k=0;$k<=9;$k++){
        //             $number = $i.$j.$k;
        //             Number::create([
        //                 'clock_id'=>5,
        //                 'lottery_type_id'=>3,
        //                 'number' => $number,
        //                 'sell'=> 100000,
        //                 'demand'=>0,
        //                 'disable'=>0,
        //             ]);
        //         }
        //     }
        // }

        
        for($j = 0;$j<31;$j++){
            $day = $j+1;
            // for($i = 1; $i<5 ;$i++){
            //     Lottery::create([
            //         'lottery_type_id' => 1,
            //         'clock_id' => $i,
            //         'number' => rand(10,99),
            //         'created_at' =>"2024-09-$day 14:43:56",
            //         'updated_at' =>"2024-09-$day 14:43:56",
            //     ]);
            // }

            // for($i = 1; $i<5 ;$i++){
            //     if($i == 2 || $i == 4){
            //         Lottery::create([
            //             'lottery_type_id' => 2,
            //             'clock_id' => $i,
            //             'number' => rand(10,99),
            //             'created_at' =>"2024-11-$day 14:43:56",
            //             'updated_at' =>"2024-11-$day 14:43:56",
            //         ]);
            //     }
            // }

            // if($j == 0 || $j == 14){
            //     Lottery::create([
            //         'lottery_type_id' => 3,
            //         'clock_id' => 4,
            //         'number' => rand(100,999),
            //         'created_at' =>"2024-09-$day 14:43:56",
            //         'updated_at' =>"2024-09-$day 14:43:56",
            //     ]);
            // }
        }

        //  for($i = 1; $i<9 ;$i++){
        //         Lottery::create([
        //             'lottery_type_id' => 3,
        //             'clock_id' => 4,
        //             'number' => rand(100,999),
        //             'created_at' =>"2024-$i-1 14:43:56",
        //             'updated_at' =>"2024-$i-1 14:43:56",
        //         ]);

        //         Lottery::create([
        //             'lottery_type_id' => 3,
        //             'clock_id' => 4,
        //             'number' => rand(100,999),
        //             'created_at' =>"2024-$i-15 14:43:56",
        //             'updated_at' =>"2024-$i-14 14:43:56",
        //         ]);
        //  }

       // User::factory(100)->create();
     //  Transaction::factory(30)->create();
      //  Withdraw::factory(50)->create();
      //  Voucher::factory(200)->create();
    }
}
