<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class ContractPayment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $fillable = [
        'invoice_file_name','accounting_statement_file_name','percent','currency','payment_date','in_main_currency',
        'invoice_sent','invoice_sent_at','invoice_file','accounting_statement_file','price'
    ];

    protected $casts = [
        'invoice_sent' => 'boolean'
    ];

    public function project(){
        return $this->belongsTo(Project::class);
    }

    protected $auditExclude = [
        'id', 'invoice_file', 'invoice_file_name', 'accounting_statement_file', 'accounting_statement_file_name', 'project_id'
    ];
    
    public function transformAudit(array $data): array
    {
        return $data;
    }

    public function generateTags(): array
    {
        return ['project:'.$this->project_id];
    }
}
