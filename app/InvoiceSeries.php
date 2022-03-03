<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceSeries extends Model
{
    protected $table = 'invoice_series';

    protected $fillable = ['name', 'document_type_id'];
}
