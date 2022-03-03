<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class ProjectAdditionalContract extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['contract_number', 'contract_file', 'contract_file_name'];

    protected $auditExclude = [
        'id', 'contract_file', 'project_id'
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
