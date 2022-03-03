<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceStateActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_state_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('order');
            $table->integer('service_state_id');
            $table->integer('type'); // 1 - Email, 2 - Sms, 3 - Task
            // Email
            $table->integer('email_type'); // 1 - Client, 2 - User, 3 - Fix address, 4 - Task responsible
            $table->string('email_address')->nullable();
            $table->boolean('email_cc')->default(false); // Copy to sender
            $table->string('email_subject')->nullable();
            $table->text('email_text')->nullable();
            $table->string('email_file')->nullable();
            $table->string('email_filename')->nullable();
            $table->integer('email_include_estimate_type'); // 0 - not attach, 1 - master estimate, 2 - selected
            $table->boolean('email_include_resource_attachments')->default(false);
            $table->integer('email_include_checklist_id'); // 0 - not attach
            $table->boolean('email_show')->default(true);
            // Sms
            $table->integer('sms_type'); // 1 - Client, 2 - Fix phone
            $table->string('sms_from')->nullable();
            $table->string('sms_to')->nullable();
            $table->string('sms_text')->nullable();
            // Task
            $table->integer('event_type_id'); // 0 - selected
            $table->integer('event_user_id'); // 0 - selected
            $table->integer('event_date_type'); // 0 - selected
            $table->text('event_description')->nullable();
            $table->boolean('event_description_not_editable')->default(false);
            // Indexes
            $table->index('service_state_id');
            $table->index('event_type_id');
            $table->index('event_user_id');
            $table->index('email_include_checklist_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_state_actions');
    }
}
