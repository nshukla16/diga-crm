<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class CustomsDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['name', 'exist', 'date', 'file', 'file_name'];

    protected $casts = [
        'exist' => 'boolean'
    ];

    protected $auditExclude = [
        'id', 'file', 'file_name', 'manufacturer_order_id'
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
