<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class ManufacturerPayment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'invoice_file_name','accounting_statement_file_name','percent','currency','payment_date','in_main_currency',
        'confirmed','confirmed_date','invoice_file','accounting_statement_file','price'
    ];

    protected $casts = [
        'confirmed' => 'boolean'
    ];

    protected $auditExclude = [
        'invoice_file', 'invoice_file_name', 'accounting_statement_file', 'accounting_statement_file_name',
        'project_manufacturer_id', 'id'
    ];

    public function transformAudit(array $data): array
    {
        return $data;
    }

    public function project_manufacturer()
    {
        return $this->belongsTo(ProjectManufacturer::class);
    }

    public function generateTags(): array
    {
        return ['project:'.$this->project_manufacturer->project_id];
    }
}
