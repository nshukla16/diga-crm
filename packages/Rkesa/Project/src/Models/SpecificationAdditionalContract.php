<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class SpecificationAdditionalContract extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable =[
        'number',
        'file',
        'file_name',
        'inner_specification_id',
        'from_db',
        'legal_entity_contract_id',
    ];

    protected $casts = [
        'from_db' => 'boolean'
    ];

    protected $auditExclude = [
        'file', 'file_name', 'inner_specification_id', 'id'
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

    public function inner_specification()
    {
        return $this->belongsTo(InnerSpecification::class);
    }

    public function generateTags(): array
    {
        return ['project:'.$this->inner_specification->project_manufacturer->project_id];
    }
}
