<?php

use Illuminate\Database\Seeder;

class SubscriptionDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('subscription_data')->insert([
            'AppId' => '2.43',
            'subscriberId' => '2',
            'otp' => '12',
            'count' => '4',
            'device_id' => '1',
            'updated_at' => new DateTime,
            'created_at' => new DateTime,
        ]);
        DB::table('subscription_data')->insert([
            'AppId' => '2.43',
            'subscriberId' => '2',
            'otp' => '12',
            'count' => '4',
            'device_id' => '1',
            'updated_at' => new DateTime,
            'created_at' => new DateTime,
        ]);
        DB::table('subscription_data')->insert([
            'AppId' => '2.43',
            'subscriberId' => '2',
            'otp' => '12',
            'count' => '4',
            'device_id' => '1',
            'updated_at' => new DateTime,
            'created_at' => new DateTime,
        ]);
    }
}
