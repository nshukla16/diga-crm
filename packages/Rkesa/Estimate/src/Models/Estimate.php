<?php

namespace Rkesa\Estimate\Models;

use Log;
use Auth;
use App\User;
use Rkesa\Service\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Rkesa\Estimate\Models\EstimateGroup;
use Rkesa\EstimateFork\Models\EstimateFork;
use Rkesa\Planning\Models\EstimatePlanning;
use Rkesa\Estimate\Models\EstimatePlanningDetail;

class Estimate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject', 'additional_price', 'deadline', 'discount', 'vat_type', 'vat_maodeobra', 'vat_material', 'vat_custom',
        'vat_text', 'price', 'service_id', 'option', 'revision', 'blocked', 'user_id'
    ];

    protected $appends = ['can_be_deleted'];

    public function lines()
    {
        return $this->hasMany(EstimateLine::class);
    }

    public function lines_with_join()
    {
        return EstimateLine::where('estimate_id', $this->id)
            ->leftJoin('estimate_line_categories', function ($join) {
                $join->on('estimate_line_categories.id', '=', 'estimate_lines.lineable_id')
                    ->where('estimate_lines.lineable_type', '=', '\App\EstimateLineCategory');
            })
            ->leftJoin('estimate_line_datas', function ($join) {
                $join->on('estimate_line_datas.id', '=', 'estimate_lines.lineable_id')
                    ->where('estimate_lines.lineable_type', '=', '\App\EstimateLineData');
            })
            ->leftJoin('estimate_units as data_units', function ($join) {
                $join->on('data_units.id', '=', 'estimate_line_datas.estimate_unit_id');
            })
            ->leftJoin('estimate_line_fichas', function ($join) {
                $join->on('estimate_line_fichas.id', '=', 'estimate_lines.lineable_id')
                    ->where('estimate_lines.lineable_type', '=', '\App\EstimateLineFicha');
            })
            ->leftJoin('estimate_units as ficha_units', function ($join) {
                $join->on('ficha_units.id', '=', 'estimate_line_fichas.estimate_unit_id');
            })
            ->leftJoin('estimate_line_separators', function ($join) {
                $join->on('estimate_line_separators.id', '=', 'estimate_lines.lineable_id')
                    ->where('estimate_lines.lineable_type', '=', '\App\EstimateLineSeparator');
            })
            ->select(
                'estimate_lines.id as id',
                'estimate_lines.order as order',
                'estimate_lines.parent_id as parent_id',
                'estimate_lines.lineable_type as lineable_type',
                'estimate_lines.lineable_id as lineable_id',
                'estimate_lines.attention as attention',
                //Separators
                'estimate_line_separators.name as separator_name',
                //Categories
                'estimate_line_categories.name as category_name',
                //Data
                'estimate_line_datas.note as data_note',
                'estimate_line_datas.description as data_description',
                'estimate_line_datas.ppu as data_ppu',
                'estimate_line_datas.quantity as data_quantity',
                'estimate_line_datas.price as data_price',
                'estimate_line_datas.estimate_unit_id as data_measure',
                'data_units.measure as data_measure_name',
                //Ficha
                'estimate_line_fichas.description as ficha_description',
                'estimate_line_fichas.note as ficha_note',
                'estimate_line_fichas.price as ficha_price',
                'estimate_line_fichas.ppu as ficha_ppu',
                'estimate_line_fichas.quantity as ficha_quantity',
                'estimate_line_fichas.estimate_unit_id as ficha_measure',
                'ficha_units.measure as ficha_measure_name'
            )
            ->orderBy('parent_id')->orderBy('order')
            ->get();
    }

    public static function search($search)
    {
        $user = Auth::user();
        $estimates = DB::table('estimates')
            ->join('services', 'services.id', '=', 'estimates.service_id')
            ->where('services.estimate_number', 'like', '%' . $search . '%')
            ->select(
                'estimates.id as id',
                'estimates.revision as revision',
                'estimates.option as option',
                'estimates.fork_id as fork_id',
                'services.estimate_number as estimate_number',
                'services.additional as additional'
            )
            ->orderBy('services.estimate_number', 'asc');

        switch ($user->cando('estimates', 'read')) {
            case 0:
                $estimates->where('id', null);
                break;
            case 1:
                $estimates->where('user_id', $user->id);
                break;
            case 2:
                $estimates->whereIn('user_id', $user->groupmates_ids());
                break;
            case 3:
                break;
        }

        return $estimates->get();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function estimate_pay_stages()
    {
        return $this->hasMany(EstimatePayStage::class)->select('id', 'percent', 'text', 'estimate_id', 'payment_date', 'vat_type', 'invoice_file', 'invoice_file_name', 'recibo_file', 'recibo_file_name', 'paid', 'invoice_number', 'fact_paid', 'email_template_id', 'proof_file', 'proof_file_name');
    }

    public function changes()
    {
        return $this->hasMany(EstimateChange::class)->orderBy('created_at', 'desc');
    }

    public function workers()
    {
        return $this->hasMany(EstimateWorker::class)->with('user');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function estimate_materials()
    {
        return $this->hasMany(EstimateMaterial::class);
    }

    public function estimate_fork()
    {
        return $this->belongsTo(EstimateFork::class, 'fork_id');
    }

    public function getCanBeDeletedAttribute()
    {
        $user = Auth::user();
        if ($user) {
            switch ($user->cando('estimates', 'delete')) {
                case 0:
                    return false;
                case 1:
                    return $this->user_id == $user->id;
                case 2:
                    return in_array($this->user_id, $user->groupmates_ids());
                case 3:
                    return true;
            }
        } else {
            return false;
        }
    }

    public static function delete_with_relations($id)
    {
        $estimate = Estimate::findOrFail($id);
        $service = $estimate->service;
        if ($estimate->id == $service->master_estimate_id) {
            $service->master_estimate_id = null;
            $service->save();
        }
        EstimatePayStage::where('estimate_id', $estimate->id)->delete();
        $data_ids = EstimateLine::where('lineable_type', '\App\EstimateLineData')->where('estimate_id', $estimate->id)->select('lineable_id')->get();
        EstimateLineData::whereIn('id', $data_ids)->delete();
        $fisha_ids = EstimateLine::where('lineable_type', '\App\EstimateLineFicha')->where('estimate_id', $estimate->id)->select('lineable_id')->get();
        EstimateLineFicha::whereIn('id', $fisha_ids)->delete();
        EstimateLineFichaResource::whereIn('estimate_line_ficha_id', $fisha_ids)->delete();
        EstimateLine::where('estimate_id', $estimate->id)->delete();

        EstimateMaterial::where('estimate_id', $estimate->id)->delete();
        EstimateWorker::where('estimate_id', $estimate->id)->delete();
        $estimate->delete();
    }

    public function get_estimate_number()
    {
        return $this->service->get_service_number() .
            ($this->option != "" ? " " . trans('template.option') . $this->option : "") .
            ($this->revision != "" ? " " . trans('template.revision') . $this->revision : "") .
            ($this->fork_id != null ? " " . $this->estimate_fork->name : "");
    }

    public function gantts()
    {
        return $this->hasMany(EstimatePlanning::class, 'estimate_id');
    }

    public function estimate_details()
    {
        return $this->hasOne(EstimatePlanningDetail::class);
    }

    public function groups()
    {
        return $this->hasMany(EstimateGroup::class, 'estimate_id');
    }
}
