<?php

namespace Rkesa\Dashboard\Models;

use App\GlobalSettings;
use Illuminate\Database\Eloquent\Model;
use Rkesa\Service\Models\ServiceState;
use Rkesa\Dashboard\Models\DashboardEntity;

class Dashboard extends Model
{
    protected $table = 'dashboards';
    protected $fillable = ['name'];

    public static function validation() {
        $available_statuses = ServiceState::all()->count();
        
        $rules = array(
            'name' => 'required',
            'entities' => "required|array|size:$available_statuses",
            'entities.*.hide' => 'required|boolean',
            'entities.*.fields.*.type' => "required|integer|min:1|max:13",
            // 'widgets' => "array|min:0|max:9",
            'widgets.*.widget_type' => "required|integer|min:0|max:5",
            'widgets.*.data_type' => "required|integer|min:0|max:10",
            'widgets.*.size' => "required|min:1|max:2|integer"
        );

        $messages = array(
            'entities.size' => trans("dashboard.Should_be_exact_count_of_entities", ["count" => $available_statuses]),
            'entities.*.type' => trans("dashboard.Should_be_from_1_to_13")
        );

        return (object) [
            'rules' => $rules,
            'messages' => $messages
        ];
    }

    public function entities()
    {
        return $this->hasMany(DashboardEntity::class);
    }

    public function widgets()
    {
        return  $this->hasMany(DashboardWidget::class);
    }

}
