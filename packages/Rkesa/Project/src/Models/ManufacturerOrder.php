<?php

namespace Rkesa\Project\Models;

use App\User;
use Illuminate\Support\Arr;
use Rkesa\Client\Models\ClientContact;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Rkesa\Project\Models\TransportationPayment;
use Rkesa\Project\Models\TransportationVatPayment;

class ManufacturerOrder extends Model implements Auditable // it should be ManufacturerRequest, not ManufacturerOrder
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'number',
        'order_date',
        'sender_manufacturer_id',
        'sender_legal_address',
        'uploading_address',
        'manufacturer_contact_name',
        'manufacturer_contact_id',
        'manufacturer_contact_phone',
        'manufacturer_contact_email',
        'loading_ready_date',
        'order_contract_and_specifications',
        'conditions_of_delivery',
        'additional_loading',
        'shipment_place',
        'destination_place',
        'shipment_type_and_counts',
        'consignment_receiver_company_name',
        'consignment_receiver_address',
        'consignment_receiver_phone',
//            'client_id',
        'client_contract_number',
        'downloading_address',
        //'downloading_contact_phone',
        'downloading_contact_id',
        'manufacturer_id',
        'manufacturer_legal_address',
        'loading_name',
        'loading_selling_price',
        'loading_cost_price',
        'inner_specification_number',
        'project_id',
        'project_manufacturer_id',
        'manufacturer_name',
        'final_buyer',
        'comment_carrier',
        // Customs process
        'customs_application', 'customs_application_date', 'customs_issue', 'customs_issue_date', 'dt',
        'dt_file', 'dt_file_name', 'approximate_date_of_arrival_to_temporary', 'approximate_date_of_arrival',
        'fact_delivery', 'fact_delivery_date',
        'transportation_total',
        'transportation_vat_total'

    ];

    protected $casts = [
        'customs_application' => 'boolean',
        'customs_issue' => 'boolean',
        'fact_delivery' => 'boolean',
    ];

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function project_manufacturer()
    {
        return $this->belongsTo(ProjectManufacturer::class);
    }

    public function sender_manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'sender_manufacturer_id');
    }

    public function managers()
    {
        return $this->hasMany(ManufacturerOrderManager::class);
    }

    public function places()
    {
        return $this->hasMany(ManufacturerOrderPlace::class);
    }

    public function downloading_contact()
    {
        return $this->belongsTo(ClientContact::class);
    }

    public function carriers()
    {
        return $this->hasMany(ProjectCarrier::class);
    }

    public function customs_documents()
    {
        return $this->hasMany(CustomsDocument::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function transportation_payments()
    {
        return $this->hasMany(TransportationPayment::class);
    }

    public function transportation_vat_payments()
    {
        return $this->hasMany(TransportationVatPayment::class);
    }

    public function generate_order_number()
    {
        $pm = ProjectManufacturer::where(['manufacturer_id' => $this->sender_manufacturer_id, 'project_id' => $this->project_id])->first();
        $le_id = null;
        if ($pm && $pm->buyer_legal_entity_id){ // if we have legal entity in manufacturer payment steps -> use it
            $le_id = $pm->buyer_legal_entity_id;
        }else{
            $p = Project::find($this->project_id); // if not -> use seller legal entity from project card
            $le_id = $p->seller_legal_entity_id;
        }

        $le = LegalEntity::find($le_id);
        $le->last_logistic_order_number++;
        $le->save();

        $format = $le->logistic_order_number_format;
        $format = str_replace('{%n}', $le->last_logistic_order_number, $format);
        $format = str_replace('{%m}', date("m"), $format);
        $format = str_replace('{%y}', date("y"), $format);
        $this->number = $format;
        return $this->number;
    }

    protected $auditExclude = [
        'sender_manufacturer_id', 'project_manufacturer_id', 'project_id', 'downloading_contact_id', 'id',
        'dt_file', 'dt_file_name'
    ];

    public function transformAudit(array $data): array
    {
        return $data;
    }

    public function generateTags(): array
    {
        return ['project:'.$this->project_manufacturer->project_id];
    }
}
