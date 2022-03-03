<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDatesToProjectManufacturers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->dateTimeTz('designated_shipping_date')->nullable();
            $table->dateTimeTz('fact_shipping_date')->nullable();
            $table->text('comment_preparation_steps');
            $table->text('comment_manufacturer_payments');
            $table->dateTimeTz('order_date')->nullable();
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
            $table->dropColumn(['designated_shipping_date', 'fact_shipping_date', 'preparation_steps_notes', 'order_date']);
        });
    }
}
