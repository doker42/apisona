<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PassportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => 1,
                'user_id' => null,
                'name' => 'users',
                'secret' => 'G9Z1uNDc7m6tYzmcA9VL92ucuqwfSCPiwVyn0Ci5',
                'provider' => 'users',
                'redirect' => 'http://localhost',
                'personal_access_client' => 0,
                'password_client' => 1,
                'revoked' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),

            ],
        ];

        DB::table('oauth_clients')->insert($data);
    }
}
