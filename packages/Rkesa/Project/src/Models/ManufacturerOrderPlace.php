<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class ManufacturerOrderPlace extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['text'];

    protected $auditExclude = [
        'manufacturer_order_id', 'id'
    ];

    public function transformAudit(array $data): array
    {
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
