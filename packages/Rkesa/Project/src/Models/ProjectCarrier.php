<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class ProjectCarrier extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'carrier_id', 'contract_file', 'contract_file_name', 'contract_number', 'from_db', 'carrier_contract_id'
    ];

    protected $casts = [
        'from_db' => 'boolean'
    ];

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    protected $auditExclude = [
        'contract_file', 'contract_file_name', 'manufacturer_order_id', 'id',
    ];

    public function transformAudit(array $data): array
    {
        if (Arr::has($data, 'old_values.carrier_id')) {
            if ($data['old_values']['carrier_id']) {
                $data['old_values']['carrier_id'] = Carrier::find($data['old_values']['carrier_id'])->name;
            }
        }
        if (Arr::has($data, 'new_values.carrier_id')) {
            if ($data['new_values']['carrier_id']) {
                $data['new_values']['carrier_id'] = Carrier::find($data['new_values']['carrier_id'])->name;
            }
        }

        if (Arr::has($data, 'old_values.carrier_contract_id')) {
            if ($data['old_values']['carrier_contract_id']) {
                $data['old_values']['carrier_contract_id'] = CarrierContract::find($data['old_values']['carrier_contract_id'])->name;
            }
        }
        if (Arr::has($data, 'new_values.carrier_contract_id')) {
            if ($data['new_values']['carrier_contract_id']) {
                $data['new_values']['carrier_contract_id'] = CarrierContract::find($data['new_values']['carrier_contract_id'])->name;
            }
        }
        return $data;
    }

    public function manufacturer_order()
    {
        return $this->belongsTo(ManufacturerOrder::class);
    }

    public function generateTags(): array
    {
        return ['project:'.$this->manufacturer_order->project_manufacturer->project_id];
    }
}
