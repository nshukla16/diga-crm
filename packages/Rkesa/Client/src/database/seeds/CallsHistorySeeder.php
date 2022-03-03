<?php

namespace Rkesa\Client\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class CallsHistorySeeder extends Seeder {

    public function run()
    {
        DB::table('client_history')->insert(array(
			array(
				'user_id' => 1,
				'type_id' => 21,
				'client_contact_id' => 1,
				'message' => 'system',
				'created_at' => Carbon::now(),
				'call_id' => '101010101.10101',
				'link'	  => 'http://www.noiseaddicts.com/samples_1w72b820/156.mp3'
			),
			array(
				'user_id' => 1,
				'type_id' => 21,
				'client_contact_id' => 1,
				'message' => 'system',
				'created_at' => Carbon::now(),
				'call_id' => '101010101.10102',
				'link'	  => 'http://www.noiseaddicts.com/samples_1w72b820/95.mp3'
			),
			array(
				'user_id' => 1,
				'type_id' => 22,
				'client_contact_id' => 1,
				'message' => 'system',
				'created_at' => Carbon::now(),
				'call_id' => '101010101.10103',
				'link'	  => 'http://www.noiseaddicts.com/samples_1w72b820/17.mp3'
			),
			array(
				'user_id' => 1,
				'type_id' => 22,
				'client_contact_id' => 1,
				'message' => 'system',
				'created_at' => Carbon::now(),
				'call_id' => '101010101.10104',
				'link'	  => 'http://www.noiseaddicts.com/samples_1w72b820/106.mp3'
			)
        ));
    }
}