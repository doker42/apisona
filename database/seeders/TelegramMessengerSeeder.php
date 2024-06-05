<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TelegramMessengerSeeder extends Seeder
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
                'id'   => 1,
                'slug' => 'telegram',
                'name' => 'Telegram',
                'description' => 'Telegram bot messenger',
                'active'      => false,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ]
        ];
        DB::table('messengers')->insert($data);
    }
}
