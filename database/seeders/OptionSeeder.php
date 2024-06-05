<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $options = array(
            array('id' => 1, 'name' => 'people',             'type' => 'string', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 2, 'name' => 'projects',           'type' => 'string', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 3, 'name' => 'storage',            'type' => 'string', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 4, 'name' => 'start_analytics',    'type' => 'bool',   'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 5, 'name' => 'white_label',        'type' => 'bool',   'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 6, 'name' => 'conditionally_free', 'type' => 'string', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 7, 'name' => 'quick_import',       'type' => 'bool',   'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 8, 'name' => 'service',            'type' => 'bool',   'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 9, 'name' => 'admins',             'type' => 'string', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 10,'name' => 'early_access',       'type' => 'bool',   'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => 11,'name' => 'architect_support',  'type' => 'bool',   'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'deleted_at' => null),
        );
        DB::table('options')->insert($options);
    }
}
