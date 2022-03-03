<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use OwenIt\Auditing\Contracts\Auditable;

class ManufacturerCommissionRelation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'commission_need_to_pay',
        'comment_commission',
        'currency',
        'legal_entity_id'
    ];

    protected $auditExclude = [
        'id'
    ];

    public function transformAudit(array $data): array
    {
        if (Arr::has($data, 'old_values.legal_entity_id')) {
            $data['old_values']['legal_entity_id'] = LegalEntity::find($data['old_values']['legal_entity_id'])->name;
        }
        if (Arr::has($data, 'new_values.legal_entity_id')) {
            $data['new_values']['legal_entity_id'] = LegalEntity::find($data['new_values']['legal_entity_id'])->name;
        }

        if (Arr::has($data, 'old_values.project_manufacturer_id')) {
            $data['old_values']['project_manufacturer_id'] = ProjectManufacturer::find($data['old_values']['project_manufacturer_id'])->manufacturer->name;
        }
        if (Arr::has($data, 'new_values.project_manufacturer_id')) {
            $data['new_values']['project_manufacturer_id'] = ProjectManufacturer::find($data['new_values']['project_manufacturer_id'])->manufacturer->name;
        }
        return $data;
    }

    public function commission_payments()
    {
        return $this->hasMany(CommissionPayment::class);
    }

    public function documents()
    {
        return $this->hasMany(CommissionRelationDocument::class);
    }

    public function legal_entity()
    {
        return $this->belongsTo(LegalEntity::class);
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
