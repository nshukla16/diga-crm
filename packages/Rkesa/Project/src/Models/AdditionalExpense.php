<?php

namespace Rkesa\Project\Models;

use Illuminate\Support\Arr;
use Rkesa\Project\Models\Project;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AdditionalExpense extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name', 'project_id','price','currency','payment_date','in_main_currency', 'document_file', 'document_file_name', 'comment'
    ];

    protected $casts = [
    ];

    protected $auditExclude = [
        'document_file', 'project_id', 'id',
    ];

    public function transformAudit(array $data): array
    {
        return $data;
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function generateTags(): array
    {
        return ['project:'.$this->project_id];
    }
}
