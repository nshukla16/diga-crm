<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Estimate\Models\EstimateGroupWorker;

class EstimateGroupWorkerActivity extends Model
{
    protected $table = 'estimate_group_workers_activities';

    protected $fillable = [
        'estimate_group_worker_id', 'resource_id', 'estimate_line_category_id', 'estimate_unit_id', 'quantity'
    ];

    public function estimate_group_worker()
    {
        return $this->belongsTo(EstimateGroupWorker::class);
    }
}
