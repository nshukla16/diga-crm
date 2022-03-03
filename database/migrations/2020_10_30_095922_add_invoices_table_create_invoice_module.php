<?php

use App\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoicesTableCreateInvoiceModule extends Migration
{
    public function up()
    {
        DB::table('modules')->insert([
            ['name' => 'invoices', 'enabled' => false],
        ]);

        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->date('invoice_date');
            $table->dateTime('system_entry_date');
            $table->string('invoice_no');
            $table->double('gross_total');

            $table->string('hash');
            $table->text('signature');

            $table->string('filename');
            $table->string('url');

            $table->timestamps();
        });
    }

    public function down()
    {
        $module = Module::where('name', 'invoices')->first();
        $module->delete();

        Schema::dropIfExists('invoices');
    }
}
