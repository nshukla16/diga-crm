<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class ProjectManufacturerAdditionalDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'document_name', 'exist', 'document_date', 'document_file', 'document_file_name'
    ];

    protected $casts = [
        'exist' => 'boolean'
    ];

    protected $auditExclude = [
        'document_file', 'project_manufacturer_id', 'id'
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
