<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetNullToEventsFinishWhereNeed extends Migration
{
    public function up()
    {
        $results = DB::table('events')->where('finish', '0000-00-00 00:00:00')->select('id','finish')->get();

        foreach ($results as $result){
            DB::table('events')
                ->where('id',$result->id)
                ->update([
                    "finish" => null
            ]);
        }
    }

    public function down()
    {
        $results = DB::table('events')->where('finish', null)->select('id','finish')->get();

        foreach ($results as $result){
            DB::table('events')
                ->where('id',$result->id)
                ->update([
                    "finish" => '0000-00-00 00:00:00'
            ]);
        }
    }
}
