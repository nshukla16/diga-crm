<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class ProjectSpecification extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $fillable = [
        'name', 'notes'
    ];

    protected $auditExclude = [
        'id', 'project_id'
    ];

    public function transformAudit(array $data): array
    {
        return $data;
    }

    public function equipment()
    {
        return $this->hasMany(ProjectSpecificationEquipment::class);
    }

    public function generateTags(): array
    {
        return ['project:'.$this->project_id];
    }
}
