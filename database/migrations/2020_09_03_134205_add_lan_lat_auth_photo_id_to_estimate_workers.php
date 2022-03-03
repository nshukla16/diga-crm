<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLanLatAuthPhotoIdToEstimateWorkers extends Migration
{
    public function up()
    {
        Schema::table('estimate_group_workers', function (Blueprint $table) {
            $table->boolean('is_suspicious')->nullable();
            $table->boolean('is_approved')->nullable();
            $table->integer('service_id')->nullable();
        });

        Schema::table('auth_photos', function (Blueprint $table) {
            $table->float('lat')->nullable();
            $table->float('lng')->nullable();
            $table->integer('estimate_group_worker_id')->nullable();
            $table->string('type')->nullable();
        });
    }

    public function down()
    {
        Schema::table('estimate_group_workers', function (Blueprint $table) {
            $table->dropColumn('is_suspicious');
            $table->dropColumn('is_approved');
            $table->dropColumn('service_id');
        });
        Schema::table('auth_photos', function (Blueprint $table) {
            $table->dropColumn('lat');
            $table->dropColumn('lng');
            $table->dropColumn('estimate_group_worker_id');
            $table->dropColumn('type');
        });
    }
}
