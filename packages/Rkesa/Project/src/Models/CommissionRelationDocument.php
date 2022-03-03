<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CommissionRelationDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['name', 'file', 'file_name', 'manufacturer_commission_relation_id'];

    protected $auditExclude = [
        'id', 'file', 'manufacturer_commission_relation_id'
    ];

    public function manufacturer_commission_relation()
    {
        return $this->belongsTo(ManufacturerCommissionRelation::class);
    }

    public function generateTags(): array
    {
        return ['project:'.$this->manufacturer_commission_relation->project_manufacturer->project_id];
    }
}
