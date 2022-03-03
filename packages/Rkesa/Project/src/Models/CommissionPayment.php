<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CommissionPayment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'invoice_file_name','accounting_statement_file_name','percent','currency','payment_date','in_main_currency',
        'invoice_sent','invoice_sent_at','invoice_file','accounting_statement_file','price'
    ];

    protected $auditExclude = [
        'id', 'invoice_file', 'accounting_statement_file', 'manufacturer_commission_relation_id'
    ];

    public function manufacturer_commission_relation()
    {
        return $this->belongsTo(ManufacturerCommissionRelation::class);
    }

    public function generateTags(): array
    {
        return ['project:'.$this->manufacturer_commission_relation->project_manufacturer->project_id];
    }
}
