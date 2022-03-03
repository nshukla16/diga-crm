<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletionToInvoices extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_canceled')->nullable();
            $table->string('canceling_reason')->nullable();
            $table->boolean('is_exported')->nullable();            
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('is_canceled');
            $table->dropColumn('canceling_reason');
            $table->dropColumn('is_exported');
        });
    }
}
