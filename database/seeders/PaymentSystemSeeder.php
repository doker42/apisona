<?php

namespace Database\Seeders;

use App\Models\PaymentSystem;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        \App\Models\PaymentSystem::truncate();
        Schema::enableForeignKeyConstraints();
        $data = [
            [
                'id' => 1,
                'name' => 'Fondy',
                'slug' => 'fondy',
                'description' => 'Fondy description',
                'status' => PaymentSystem::STATUS_ACTIVE,
                'callback_url' => 'v1/integration/payments/callback',
                'response_url' => 'v1/integration/payments/response',
                'default' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Stripe',
                'slug' => 'stripe',
                'description' => 'Stripe description',
                'status' => PaymentSystem::STATUS_ACTIVE,
                'callback_url' => 'v1/integration/payments/callback',
                'response_url' => 'v1/integration/payments/response',
                'default' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('payment_systems')->insert($data);
    }
}
