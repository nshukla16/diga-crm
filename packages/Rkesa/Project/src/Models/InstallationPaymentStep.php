<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class InstallationPaymentStep extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'id',
        'project_id',
        'payment_name',
        'payment_value',
        'payment_currency',
        'payment_date',
        'payment_invoice_sent',
        'payment_main_currency',
        'payment_invoice_file_name',
        'payment_invoice_file_path',
        'payment_accounting_file_name',
        'payment_accounting_file_path',
        'payment_confirmed',
        'payment_confirmed_date'
    ];

    protected $casts = [
        'payment_invoice_sent' => 'boolean',
        'payment_confirmed' => 'boolean',
    ];

    protected $auditExclude = [
        'id', 'project_id', 'payment_accounting_file_path', 'payment_invoice_file_path',
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
