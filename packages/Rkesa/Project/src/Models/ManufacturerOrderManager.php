<?php

namespace Rkesa\Project\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class ManufacturerOrderManager extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $auditExclude = [
        'manufacturer_order_id', 'id'
    ];

    public function transformAudit(array $data): array
    {
        if (Arr::has($data, 'old_values.user_id')) {
            if ($data['old_values']['user_id']) {
                $data['old_values']['user_id'] = User::find($data['old_values']['user_id'])->name;
            }
        }
        if (Arr::has($data, 'new_values.user_id')) {
            if ($data['new_values']['user_id']) {
                $data['new_values']['user_id'] = User::find($data['new_values']['user_id'])->name;
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
