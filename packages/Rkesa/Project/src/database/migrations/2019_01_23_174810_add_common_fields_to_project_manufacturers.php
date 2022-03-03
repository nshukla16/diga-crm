<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommonFieldsToProjectManufacturers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->string('order_number');
            $table->boolean('order_agreed');
            $table->dateTimeTz('order_agreed_at')->nullable();
            $table->boolean('order_sent');
            $table->dateTimeTz('order_sent_at')->nullable();
            $table->string('order_sent_file')->nullable();
            $table->string('order_sent_file_name')->nullable();
            $table->boolean('order_confirmed');
            $table->dateTimeTz('order_confirmed_at')->nullable();
            $table->string('order_confirmed_file')->nullable();
            $table->string('order_confirmed_file_name')->nullable();
            $table->integer('limit_forming_type')->nullable();
            $table->integer('limit_forming_date')->nullable();
            $table->integer('limit_forming_days')->nullable();
            $table->dateTimeTz('limit_before_date')->nullable();
            $table->text('comment_main');
            $table->text('comment_limits');
            //
            $table->string('contract_number');
            $table->string('contract_file')->nullable();
            $table->string('contract_file_name')->nullable();
            $table->dateTimeTz('contract_signed_date');
            // specifications
            $table->integer('conditions_of_delivery');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->dropColumn(['order_number',
                'order_agreed',
                'order_agreed_at',
                'order_sent',
                'order_sent_at',
                'order_sent_file',
                'order_sent_file_name',
                'order_confirmed',
                'order_confirmed_at',
                'order_confirmed_file',
                'order_confirmed_file_name',
                'limit_forming_type',
                'limit_forming_date',
                'limit_forming_days',
                'limit_before_date',
                'comment_main',
                'comment_limits',
                'contract_number',
                'contract_file',
                'contract_file_name',
                'contract_signed_date',
                'conditions_of_delivery']);
        });
    }
}
