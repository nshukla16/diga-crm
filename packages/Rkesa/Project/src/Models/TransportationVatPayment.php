<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class TransportationVatPayment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name','price','currency','payment_date','in_main_currency','invoice_sent','invoice_file','accounting_statement_file',
        'confirmed','confirmed_date','invoice_file_name','accounting_statement_file_name', 'document_file', 'document_file_name'
    ];

    protected $casts = [
        'confirmed' => 'boolean',
        'invoice_sent' => 'boolean'
    ];

    protected $auditExclude = [
        'invoice_file', 'accounting_statement_file',
        'manufacturer_order_id', 'id', 'document_file',
    ];

    public function transformAudit(array $data): array
    {
        return $data;
    }

    public function manufacturer_order()
    {
        return $this->belongsTo(ManufacturerOrder::class);
    }

    public function generateTags(): array
    {
        return ['project:'.$this->manufacturer_order->project_manufacturer->project_id];
    }
}
