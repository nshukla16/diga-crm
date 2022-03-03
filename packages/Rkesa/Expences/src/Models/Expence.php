<?php

namespace Rkesa\Expences\Models;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Estimate\Models\Estimate;

class Expence extends Model
{
    protected $fillable = ['invoice_number', 'supplier', 'date', 'total_without_vat', 'vat_type', 'total', 'invoice_file', 'invoice_file_name', 'service_id', 'client_contact_id', 'estimate_id'];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
}
