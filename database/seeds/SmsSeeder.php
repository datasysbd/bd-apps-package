<?php

use Illuminate\Database\Seeder;

class SmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        DB::table('sms')->insert([
            'version' => '2.43',
            'applicationId' => '2',
            'subscriberId' => '3',
            'status' => 'Active',
            'frequency' => '17hz',
            'timeStamp' => 'encoded information',
            'updated_at' => new DateTime,
            'created_at' => new DateTime,
        ]);
        DB::table('sms')->insert([
            'version' => '2.43',
            'applicationId' => '2',
            'subscriberId' => '3',
            'status' => 'Active',
            'frequency' => '17hz',
            'timeStamp' => 'encoded better information',
            'updated_at' => new DateTime,
            'created_at' => new DateTime,
        ]);
        DB::table('sms')->insert([
            'version' => '2.43',
            'applicationId' => '2',
            'subscriberId' => '3',
            'status' => 'Active',
            'frequency' => '17hz',
            'timeStamp' => 'encoded foul information',
            'updated_at' => new DateTime,
            'created_at' => new DateTime,
        ]);
    }
}
