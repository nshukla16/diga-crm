<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommentToAdditionalExpenses extends Migration
{
    public function up()
    {
        Schema::table('additional_expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('additional_expenses', 'comment')) {
                $table->text('comment')->nullable();
            }            
        });
    }

    public function down()
    {
        Schema::table('additional_expenses', function (Blueprint $table) {
            $table->dropColumn('comment');
        });
    }
}
