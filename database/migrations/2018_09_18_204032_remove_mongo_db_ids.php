<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveMongoDbIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function($table)
        {
            $table->dropColumn('mongo_id');
        });
        Schema::table('estimates', function($table)
        {
            $table->dropColumn('mongo_id');
        });
        Schema::table('clients', function($table)
        {
            $table->dropColumn('mongo_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function($table)
        {
            $table->string('mongo_id');
        });
        Schema::table('estimates', function($table)
        {
            $table->string('mongo_id');
        });
        Schema::table('clients', function($table)
        {
            $table->string('mongo_id');
        });
    }
}
