<?php

use App\Invoice;
use App\InvoiceDocumentType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

class RemoveInvoiceDocumentTypes extends Migration
{
    public function up()
    {
        try {
            $document_types = InvoiceDocumentType::whereIn('code', ['FS', 'ORC', 'FP'])->delete();
        } catch (Exception $ex) {
            Log::info($ex);
        }
    }

    public function down()
    {
    }
}
