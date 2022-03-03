<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo')->nullable();
            $table->string('address')->nullable();
            $table->string('postal')->nullable();
            $table->string('nation')->nullable();
            $table->string('birthday')->nullable();
            $table->string('identical_number')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('bank_number')->nullable();
            $table->string('education')->nullable();
            $table->string('driver_number')->nullable();
            $table->string('languages')->nullable();
            $table->string('cell_phone')->nullable();
            $table->string('additional_email')->nullable();
            $table->string('home_phone')->nullable();
            $table->string('dependencies')->nullable();
            $table->string('emergency_name')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_relation')->nullable();
            $table->float('salary');
            $table->integer('head_id')->nullable();
            $table->string('insurance_number')->nullable();
            $table->string('medical_date')->nullable();
            $table->string('social_security_number')->nullable();
            $table->boolean('salary_type');
            $table->boolean('active')->default(true);
            // Indexes
            $table->index('head_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_head_id_index');
            $table->dropColumn(['photo', 'address', 'postal', 'nation', 'birthday', 'identical_number',
                'tax_number', 'bank_number', 'education', 'driver_number', 'languages', 'cell_phone',
                'additional_email', 'home_phone', 'dependencies', 'emergency_name', 'emergency_contact',
                'emergency_relation', 'salary', 'head_id', 'insurance_number', 'medical_date', 'social_security_number',
                'salary_type','active']);
        });
    }
}
