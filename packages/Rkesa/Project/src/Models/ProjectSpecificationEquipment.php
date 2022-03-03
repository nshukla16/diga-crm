<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;
use Rkesa\Estimate\Models\EstimateUnit;

class ProjectSpecificationEquipment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'name', 'size', 'estimate_unit_id', 'model', 'vendor_code', 'count', 'manufacturer_id'
    ];

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    protected $auditExclude = [
        'id', 'project_specification_id', 'manufacturer_id',
    ];

    public function transformAudit(array $data): array
    {        
        if (Arr::has($data, 'old_values.estimate_unit_id')) {
            $data['old_values']['estimate_unit_id'] = EstimateUnit::find($data['old_values']['estimate_unit_id'])->measure;
        }
        if (Arr::has($data, 'new_values.estimate_unit_id')) {
            $data['new_values']['estimate_unit_id'] = EstimateUnit::find($data['new_values']['estimate_unit_id'])->measure;
        }

        return $data;
    }

    public function project_specification()
    {
        return $this->belongsTo(ProjectSpecification::class);
    }

    public function generateTags(): array
    {
        return ['project:'.$this->project_specification->project_id];
    }
}
