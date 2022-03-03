<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class ManufacturerSpecification extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'number', 'file', 'file_name', 'signed_date', 'from_db', 'manufacturer_contract_id'
    ];

    protected $casts = [
        'from_db' => 'boolean'
    ];

    protected $auditExclude = [
        'file', 'file_name', 'project_manufacturer_id', 'id'
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
