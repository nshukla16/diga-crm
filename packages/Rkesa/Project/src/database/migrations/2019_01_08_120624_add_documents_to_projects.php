<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocumentsToProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('ready_notification')->default(false);
            $table->dateTimeTz('ready_notification_date')->nullable();
            $table->string('ready_notification_file_path')->nullable();
            $table->string('ready_notification_file_name')->nullable();
            //
            $table->boolean('acceptance_certificate')->default(false);
            $table->dateTimeTz('acceptance_certificate_date')->nullable();
            $table->string('acceptance_certificate_file_path')->nullable();
            $table->string('acceptance_certificate_file_name')->nullable();
            //
            $table->boolean('shipping_documents_sent')->default(false);
            $table->dateTimeTz('shipping_documents_sent_date')->nullable();
            $table->string('shipping_documents_sent_file_path')->nullable();
            $table->string('shipping_documents_sent_file_name')->nullable();
            //
            $table->boolean('shipping_documents_received')->default(false);
            $table->dateTimeTz('shipping_documents_received_date')->nullable();
            $table->string('shipping_documents_received_file_path')->nullable();
            $table->string('shipping_documents_received_file_name')->nullable();
            //
            $table->text('comment_documents');
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
            $table->dropColumn([
                'ready_notification', 'ready_notification_date', 'ready_notification_file_path', 'ready_notification_file_name',
                'acceptance_certificate', 'acceptance_certificate_date', 'acceptance_certificate_file_path', 'acceptance_certificate_file_name',
                'shipping_documents_sent', 'shipping_documents_sent_date', 'shipping_documents_sent_file_path', 'shipping_documents_sent_file_name',
                'shipping_documents_received', 'shipping_documents_received_date', 'shipping_documents_received_file_path', 'shipping_documents_received_file_name',
                'comment_documents'
            ]);
        });
    }
}
