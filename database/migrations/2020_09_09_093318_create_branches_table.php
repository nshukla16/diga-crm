<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->text('address');
            $table->string('postal_code');
            $table->string('city');
            $table->string('country');
            $table->string('phone');
            $table->string('fax');
            $table->string('email');            
            $table->string('web_site');
            $table->string('capital');
            $table->string('tax_number');
            $table->string('bank');
            $table->string('iban');
            $table->string('swift');
        });
        Schema::table('estimate_group_workers', function (Blueprint $table) {
            $table->integer('branch_id')->nullable();
            $table->integer('client_contact_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('branches');

        Schema::table('estimate_group_workers', function (Blueprint $table) {
            $table->dropColumn('branch_id');
            $table->dropColumn('client_contact_id');
        });
    }
}
