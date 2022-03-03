<?php

namespace App;

use App\VatType;
use App\InvoiceCode;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'description',
        'quantity',
        'unit_price',
        'discount',
        'unit',
        'invoice_id',
        'vat_type_id',
        'product_id'
    ];

    public function vat_type()
    {
        return $this->belongsTo(VatType::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
