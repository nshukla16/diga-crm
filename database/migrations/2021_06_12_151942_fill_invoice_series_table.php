<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillInvoiceSeriesTable extends Migration
{
    public function up()
    {
        $document_types = App\InvoiceDocumentType::all();

        foreach($document_types as $document_type)
        {
            $s = new App\InvoiceSeries();
            $s->document_type_id = $document_type->id;
            $s->name = "A";
            $s->save();
        }
    }

    public function down()
    {
        App\InvoiceSeries::truncate();
    }
}
