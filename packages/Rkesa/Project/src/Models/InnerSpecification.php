<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class InnerSpecification extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable =[
        'number',
        'file',
        'file_name',
        'project_manufacturer_id',
        'from_db',
        'legal_entity_contract_id',
        'delivery_conditions'
    ];

    protected $casts = [
        'from_db' => 'boolean'
    ];

    public function additional_contracts()
    {
        return $this->hasMany(SpecificationAdditionalContract::class);
    }

    protected $auditExclude = [
        'id', 'project_manufacturer_id', 'file_name', 'file'
    ];

    public function transformAudit(array $data): array
    {
        if (Arr::has($data, 'old_values.legal_entity_contract_id')) {
            if ($data['old_values']['legal_entity_contract_id']) {
                $data['old_values']['legal_entity_contract_id'] = LegalEntityContract::find($data['old_values']['legal_entity_contract_id'])->name;
            }
        }
        if (Arr::has($data, 'new_values.legal_entity_contract_id')) {
            if ($data['new_values']['legal_entity_contract_id']) {
                $data['new_values']['legal_entity_contract_id'] = LegalEntityContract::find($data['new_values']['legal_entity_contract_id'])->name;
            }
        }

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
