<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class ManufacturerAdditionalContract extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = ['contract_number', 'contract_file', 'contract_file_name', 'manufacturer_contract_id', 'from_db'];

    protected $casts = [
        'from_db' => 'boolean'
    ];

    protected $auditExclude = [
        'id', 'contract_file', 'contract_file_name', 'from_db', 'project_manufacturer_id', 'contract_number'
    ];

    public function transformAudit(array $data): array
    {
        if (Arr::has($data, 'old_values.manufacturer_contract_id')) {
            if ($data['old_values']['manufacturer_contract_id']) {
                $data['old_values']['manufacturer_contract_id'] = ManufacturerContract::find($data['old_values']['manufacturer_contract_id'])->name;
            }
        }
        if (Arr::has($data, 'new_values.manufacturer_contract_id')) {
            if ($data['new_values']['manufacturer_contract_id']) {
                $data['new_values']['manufacturer_contract_id'] = ManufacturerContract::find($data['new_values']['manufacturer_contract_id'])->name;
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
