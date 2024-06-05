<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plan_options = array(
            array('id' => 1, 'plan_id' => 1, 'option_id' => 1,  'value' => '100',     'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 2, 'plan_id' => 1, 'option_id' => 2,  'value' => '1',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 3, 'plan_id' => 1, 'option_id' => 3,  'value' => '10',      'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 4, 'plan_id' => 1, 'option_id' => 4,  'value' => '0',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 5, 'plan_id' => 1, 'option_id' => 5,  'value' => '0',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 6, 'plan_id' => 1, 'option_id' => 6,  'value' => '3',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 7, 'plan_id' => 1, 'option_id' => 7,  'value' => '0',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 8, 'plan_id' => 1, 'option_id' => 8,  'value' => '0',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 9, 'plan_id' => 1, 'option_id' => 9,  'value' => '1',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 10,'plan_id' => 1, 'option_id' => 10, 'value' => '0',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 11,'plan_id' => 1, 'option_id' => 11, 'value' => '0',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),

            array('id' => 12,'plan_id' => 2, 'option_id' => 1,  'value' => '1000',    'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 13,'plan_id' => 2, 'option_id' => 2,  'value' => '5',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 14,'plan_id' => 2, 'option_id' => 3,  'value' => '100',     'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 15,'plan_id' => 2, 'option_id' => 4,  'value' => '0',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 16,'plan_id' => 2, 'option_id' => 5,  'value' => '1',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 17,'plan_id' => 2, 'option_id' => 6,  'value' => '10',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 18,'plan_id' => 2, 'option_id' => 7,  'value' => '1',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 19,'plan_id' => 2, 'option_id' => 8,  'value' => '0',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 20,'plan_id' => 2, 'option_id' => 9,  'value' => '10',      'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 21,'plan_id' => 2, 'option_id' => 10, 'value' => '1',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 22,'plan_id' => 2, 'option_id' => 11, 'value' => '0',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),

            array('id' => 23,'plan_id' => 3, 'option_id' => 1,  'value' => '-1',      'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 24,'plan_id' => 3, 'option_id' => 2,  'value' => '-1',      'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 25,'plan_id' => 3, 'option_id' => 3,  'value' => '-1',      'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 26,'plan_id' => 3, 'option_id' => 4,  'value' => '1',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 27,'plan_id' => 3, 'option_id' => 5,  'value' => '1',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 28,'plan_id' => 3, 'option_id' => 6,  'value' => '3',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 29,'plan_id' => 3, 'option_id' => 7,  'value' => '1',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 30,'plan_id' => 3, 'option_id' => 8,  'value' => '1',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 31,'plan_id' => 3, 'option_id' => 9,  'value' => '-1',      'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 32,'plan_id' => 3, 'option_id' => 10, 'value' => '1',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 33,'plan_id' => 3, 'option_id' => 11, 'value' => '1',       'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),

        );
        DB::table('plan_option')->insert($plan_options);
    }
}
