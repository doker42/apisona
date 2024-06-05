<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = array(
            array(
                'id' => 1,
                'currency_id' => 1,
                'title' => 'Basic',
                'slug' => 'lms_tariff_1',
                'icon' => 'PeopleIcon',
                'description' => 'd_basic',
                'available' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'custom' => false
            ),
            array(
                'id' => 2,
                'currency_id' => 1,
                'title' => 'Advanced',
                'slug' => 'lms_tariff_2',
                'icon' => 'MorePeopleIcon',
                'description' => 'd_advanced',
                'available' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'custom' => false
            ),
            array(
                'id' => 3,
                'currency_id' => 1,
                'title' => 'Enterprise',
                'slug' => 'lms_tariff_3',
                'icon' => 'ElementPlusIcon',
                'description' => 'd_enterprise',
                'available' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'custom' => true
            )
        );
        DB::table('plans')->insert($plans);
    }
}
