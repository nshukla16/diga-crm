<?php

namespace Rkesa\Project\Models;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AdditionalDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'document_name', 'exist', 'document_date', 'document_file', 'document_file_name'
    ];

    protected $casts = [
        'exist' => 'boolean'
    ];

    protected $auditExclude = [
        'id', 'document_file_name', 'project_id', 'document_file'
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
