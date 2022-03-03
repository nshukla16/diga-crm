<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('estimate_pay_stages')) {
            Schema::create('estimate_pay_stages', function (Blueprint $table) {
                $table->increments('id');
                $table->double('percent');
                $table->string('text');
                $table->integer('estimate_id');
                $table->timestamps();
                // Indexes
                $table->index('estimate_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimate_pay_stages');
    }
}
