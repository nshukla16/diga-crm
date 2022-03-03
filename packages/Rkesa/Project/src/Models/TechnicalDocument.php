<?php

namespace Rkesa\Project\Models;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Rkesa\Project\Models\Project;
use Rkesa\Project\Models\Manufacturer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Contracts\Auditable;

class TechnicalDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name','days_from_prepayment_customer','document_language','days_from_prepayment_manufacturer',
        'available', 'receiving_date','translating_date','sending_date','document_file','document_file_name',
        'orig_document_file','orig_document_file_name', 'manufacturer_id'
    ];

    protected $casts = [
        'available' => 'boolean'
    ];

    protected $auditExclude = [
        'manufacturer_id', 'project_id', 'id', 'orig_document_file', 'document_file',
    ];

    protected $appends = ['customer_date', 'manufacturer_date'];

    public function transformAudit(array $data): array
    {
        return $data;
    }

    public function generateTags(): array
    {
        return ['project:'.$this->project_id];
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getCustomerDateAttribute()
    {
        return $this->customer_date();
    }

    public function getManufacturerDateAttribute()
    {
        return $this->manufacturer_date();
    }

    public function customer_date()
    {
        if ($this->project->contract_payments->all()[0]->payment_date)
        {
            return Carbon::parse($this->project->contract_payments->all()[0]->payment_date)->addDays($this->days_from_prepayment_customer)->format('d.m.y');
        } else {
            return '';
        }
    }

    public function manufacturer_date()
    {
        if (count($this->project->manufacturers) > 1)
        {
            if ($this->manufacturer_id) 
            {
                $m = $this->project->manufacturers->where('manufacturer_id', $this->manufacturer_id)->first();
                if ($m && $m->payments->all()) 
                {
                    if ($m->payments->all()[0] && $m->payments[0]->payment_date)
                    {
                        return Carbon::parse($m->payments->all()[0]->payment_date)->addDays($this->days_from_prepayment_manufacturer)->format('d.m.y');
                    }
                    else 
                    {
                        return '';
                    }
                } 
                else 
                {
                    return '';
                }
            } else {
                return '';
            }
        } 

        //Log::info($this->project->manufacturers->all()[0]->payments);

        else if ($this->project->manufacturers->all()[0]->payments && $this->project->manufacturers->all()[0]->payments->all()) 
        {
            if ($this->project->manufacturers->all()[0]->payments->all()[0] && $this->project->manufacturers->all()[0]->payments->all()[0]->payment_date)
            {
                return Carbon::parse($this->project->manufacturers->all()[0]->payments->all()[0]->payment_date)->addDays($this->days_from_prepayment_manufacturer)->format('d.m.y');
            }
            else{
                return '';
            }
        } else 
        {
            return '';
        }
    }
}
