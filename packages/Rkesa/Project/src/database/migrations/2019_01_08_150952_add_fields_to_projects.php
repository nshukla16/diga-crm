<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('comment_limits');
            $table->string('contract_file')->nullable()->change(); // change() because contract_file field is already exists
            $table->string('contract_filename')->nullable();
            $table->string('specification_file')->nullable()->change();
            $table->string('specification_filename')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['comment_limits', 'contract_filename', 'specification_filename']);
        });
    }
}
