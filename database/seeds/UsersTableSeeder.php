<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'BDApps Admin',
            'email' => 'admin@bdapps.com',
            'email_verified_at' => new DateTime,
            'password' => bcrypt('admin@bdapps.com'),
            'updated_at' => new DateTime,
            'created_at' => new DateTime,
        ]);


        DB::table('users')->insert([
            'name' => 'BDApps Admin 2',
            'email' => 'admin2@bdapps.com',
            'email_verified_at' => new DateTime,
            'password' => bcrypt('EceW%8jH+X*N6c^Fv9?u7_TG'),
            'updated_at' => new DateTime,
            'created_at' => new DateTime,
        ]);
    }
}
