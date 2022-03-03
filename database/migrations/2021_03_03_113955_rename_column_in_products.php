<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColumnInProducts extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('quanity', 'quantity');
        });
    }


    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('quantity', 'quanity');
        });
    }
}
