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
use App\Models\Holiday;

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

        User::create([
            'name' => 'Super Admin',
            'email' => 'super@babagyi.org',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'recovery_hint'=>'Super Admin',
            'admin'=>1,
            'phone' => '00',
        ]);

        User::create([
            'name' => 'Disable User',
            'email' => 'disable@babagyi.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'recovery_hint'=>'Disable User',
            'phone' => '00',
        ]);

        Clock::create([
            'hour'=>9,
            'minute'=>30,
            'second'=>0,
            'morning'=>1
        ]);

        Clock::create([
            'hour'=>12,
            'minute'=>1,
            'second'=>0,
            'morning'=>0
        ]);

        Clock::create([
            'hour'=>2,
            'minute'=>0,
            'second'=>0,
            'morning'=>0
        ]);

        Clock::create([
            'hour'=>4,
            'minute'=>30,
            'second'=>0,
            'morning'=>0
        ]);

        Clock::create([
            'hour'=>3,
            'minute'=>30,
            'second'=>0,
            'morning'=>0
        ]);

        LotteryType::create([
            'type'=>'BTC 2D',
            'coefficient'=>90,
            'open'=>1,
            'api_url'=>"",
        ]);

        LotteryType::create([
            'type'=>'Thai 2D',
            'coefficient'=>90,
            'open'=>1,
            'api_url'=>"",
        ]);

        LotteryType::create([
            'type'=>'Thai 3D',
            'coefficient'=>500,
            'open'=>1,
            'api_url'=>"",
        ]);

        Avatar::create(['url'=>'img/undraw_profile.png']);
        Avatar::create(['url'=>'img/undraw_profile_1.png']);
        Avatar::create(['url'=>'img/undraw_profile_2.png']);
        Avatar::create(['url'=>'img/undraw_profile_3.png']);

        LotteryClock::create(['lottery_type_id'=>1,'clock_id'=>1,'close_before'=>30,]);
        LotteryClock::create(['lottery_type_id'=>1,'clock_id'=>2,'close_before'=>30,]);
        LotteryClock::create(['lottery_type_id'=>1,'clock_id'=>3,'close_before'=>30,]);
        LotteryClock::create(['lottery_type_id'=>1,'clock_id'=>4,'close_before'=>30,]);
        LotteryClock::create(['lottery_type_id'=>2,'clock_id'=>2,'close_before'=>30,]);
        LotteryClock::create(['lottery_type_id'=>2,'clock_id'=>4,'close_before'=>30,]);
        LotteryClock::create(['lottery_type_id'=>3,'clock_id'=>5,'close_before'=>180,]);

        Banking::create(['bank'=>'KBZ pay','icon_url'=>'img/payment-kbz-pay.jpg']);
        Banking::create(['bank'=>'Wave pay','icon_url'=>'img/payment-wave-pay.jpg']);
        Banking::create(['bank'=>'CB pay','icon_url'=>'img/payment-cb-pay.png']);
        Banking::create(['bank'=>'AYA pay','icon_url'=>'img/payment-aya-pay.png']);

        for($i=0; $i<=9 ; $i++){
            for($j=0; $j<=9; $j++){
                $number = $i.$j;
                Number::create([
                    'clock_id'=>2,
                    'lottery_type_id'=>2,
                    'number' => $number,
                    'sell'=> 1000000,
                    'disable'=>0,
                    'demand'=>0,
                    'report'=>0,
                ]);

            }
        }

        for($i=0; $i<=9 ; $i++){
            for($j=0; $j<=9; $j++){
                $number = $i.$j;
                Number::create([
                    'clock_id'=>4,
                    'lottery_type_id'=>2,
                    'number' => $number,
                    'sell'=> 1000000,
                    'demand'=>0,
                    'disable'=>0,
                    'report'=>0,
                ]);
            }
        }

        for($i=0; $i<=9 ; $i++){
            for($j=0; $j<=9; $j++){
                for($k=0;$k<=9;$k++){
                    $number = $i.$j.$k;
                    Number::create([
                        'clock_id'=>5,
                        'lottery_type_id'=>3,
                        'number' => $number,
                        'sell'=> 100000,
                        'demand'=>0,
                        'disable'=>0,
                        'report'=>0,
                    ]);
                }
            }
        }

        Holiday::create(['title'=>'Asarnha Bucha Day','month'=>7,'day'=>26]);
        Holiday::create(['title'=>"King's Birthday",'month'=>7,'day'=>28]);
        Holiday::create(['title'=>"Mother's Day",'month'=>8,'day'=>12]);
        Holiday::create(['title'=>'Special Day','month'=>9,'day'=>24]);
        Holiday::create(['title'=>"Great Memorial Day",'month'=>10,'day'=>13]);
        Holiday::create(['title'=>"Chulalongkorn Day",'month'=>10,'day'=>22]);
        Holiday::create(['title'=>"National Day",'month'=>12,'day'=>6]);
        Holiday::create(['title'=>"Constitution Day",'month'=>12,'day'=>10]);
        Holiday::create(['title'=>"New Year's Eve",'month'=>12,'day'=>31]);
    }
}
