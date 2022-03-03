<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateUnit extends Model
{
    protected $fillable = ['measure', 'hours_to_do'];

    public $timestamps = false;

    public function estimate_line_datas()
    {
        return $this->hasMany(EstimateLineData::class);
    }

    public function estimate_line_fichas()
    {
        return $this->hasMany(EstimateLineFicha::class);
    }

    public function estimate_line_ficha_resources()
    {
        return $this->hasMany(EstimateLineFichaResource::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public static function convertUnit($un)
    {
        if (isset($un) && $un != null) {
            $un = EstimateUnit::find($un)->measure;
            switch ($un) {
                case 'm2':
                    $un = "m<sup>2</sup>";
                    break;
                case 'm3':
                    $un = "m<sup>3</sup>";
                    break;
                case 'm2/ml':
                    $un = "m<sup>2</sup>/ml";
                    break;
                default:
                    # code...
                    break;
            }
            return $un;
        } else {
            return '';
        }
    }
}
