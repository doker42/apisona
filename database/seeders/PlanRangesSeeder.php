<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanRangesSeeder extends Seeder
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
                'plan_id' => 1,
                'weight' => 1,
                'currency_id' => 1,
                'amount' => 100.00,
                'discount' => 0.00,
                'trial' => false,
                'regular_mode' => 'monthly',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'crm_product_id' => '450313000004906338',
            ],
            [
                'id' => 2,
                'plan_id' => 1,
                'weight' => 2,
                'currency_id' => 1,
                'amount' => 960.00,
                'discount' => 0.00,
                'trial' => false,
                'regular_mode' => 'yearly',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'crm_product_id' => '450313000004906337',
            ],
            [
                'id' => 3,
                'plan_id' => 2,
                'weight' => 1,
                'currency_id' => 1,
                'amount' => 200.00,
                'discount' => 0.00,
                'trial' => false,
                'regular_mode' => 'monthly',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'crm_product_id' => '450313000004906340',
            ],
            [
                'id' => 4,
                'plan_id' => 2,
                'weight' => 3,
                'currency_id' => 1,
                'amount' => 1920.00,
                'discount' => 0.00,
                'trial' => false,
                'regular_mode' => 'yearly',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'crm_product_id' => '450313000004906341',
            ],
            [
                'id' => 5,
                'plan_id' => 3,
                'weight' => 9999,
                'currency_id' => 1,
                'amount' => 0.00,
                'discount' => 0.00,
                'trial' => false,
                'regular_mode' => 'yearly',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'crm_product_id' => '450313000004906339',
            ]
        ];

        DB::table('plan_ranges')->insert($data);
    }
}
