<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class ProjectManufacturer extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'projects_manufacturers'; // the table name doesnt follow eloquent naming standart

    protected $fillable = [
        'order_number',
        'order_agreed',
        'order_agreed_at',
        'order_agreed_file',
        'order_agreed_file_name',
        'order_sent',
        'order_sent_at',
        'order_sent_file',
        'order_sent_file_name',
        'order_confirmed',
        'order_confirmed_at',
        'order_confirmed_file',
        'order_confirmed_file_name',
        'limit_forming_type',
        'limit_forming_date',
        'limit_forming_days',
        'limit_before_date',
        'comment_main',
        'comment_limits',
        'manufacturer_contract_id',
        'contract_number',
        'contract_file',
        'contract_file_name',
        'contract_signed_date',
        'conditions_of_delivery',
        //
        'buyer_legal_entity_id',
        'inner_seller_legal_entity_id',
        'inner_buyer_legal_entity_id',
        'inner_contract_number',
        'inner_contract_file',
        'inner_contract_file_name',
        'inner_contract_from_db',
        'inner_contract_legal_entity_contract_id',
        //
        'designated_shipping_date',
        'fact_shipping_date',
        'order_date',
        //
        'invoice','invoice_date','invoice_file_path','invoice_file_name',
        'packing_list','packing_list_date','packing_list_file_path','packing_list_file_name',
        'photo','photo_date','photo_file_path','photo_file_name',
        'export_declaration','export_declaration_date','export_declaration_file_path','export_declaration_file_name',
        'comment_preparation_steps', 'comment_manufacturer_payments', 'comment_commission', 'comment_inner_payments',
        //
        "warranty_period", "warranty_expiration_date", 'initial_date',
        "equipment_certificate", "equipment_certificate_date", "equipment_certificate_file_name", "equipment_certificate_file_path",
        "equipment_ex_certificate", "equipment_ex_certificate_date", "equipment_ex_certificate_file_name", "equipment_ex_certificate_file_path",
        //
        'need_to_pay', 'contract_currency', 'payments_total_price', 'inner_need_to_pay', 'commission_need_to_pay', 'inner_contract_currency',
        //
        'order_sent_contract_id',
        'order_agreed_contract_id',
        'order_confirmed_contract_id',
    ];

    protected $casts = [
        'invoice' => 'boolean',
        'packing_list' => 'boolean',
        'photo' => 'boolean',
        'export_declaration' => 'boolean',
        'order_agreed' => 'boolean',
        'order_sent' => 'boolean',
        'order_confirmed' => 'boolean',
        'equipment_certificate' => 'boolean',
        'equipment_ex_certificate' => 'boolean',
        'inner_contract_from_db' => 'boolean'
    ];

    protected $auditExclude = [
        'manufacturer_id', 'project_id', 'id', 'order_sent_file', 'order_confirmed_file', 'contract_file', 'contract_file_name',
        'order_agreed_file', 'inner_seller_legal_entity_id', 'inner_buyer_legal_entity_id', 'inner_contract_file', 'inner_contract_file_name',
        'equipment_certificate_file_path', 'equipment_ex_certificate_file_path',
    ];

    public function transformAudit(array $data): array
    {
        if (Arr::has($data, 'new_values.payments_total_price') &&
            Arr::has($data, 'old_values.payments_total_price') ) {
            if ($data['old_values']['payments_total_price'] == $data['new_values']['payments_total_price']){
                unset($data['old_values']['payments_total_price']);
                unset($data['new_values']['payments_total_price']);
            }
        }

        if (Arr::has($data, 'old_values.order_sent_contract_id')) {
            if ($data['old_values']['order_sent_contract_id']) {
                $data['old_values']['order_sent_contract_id'] = ManufacturerContract::find($data['old_values']['order_sent_contract_id'])->name;
            }
        }
        if (Arr::has($data, 'new_values.order_sent_contract_id')) {
            if ($data['new_values']['order_sent_contract_id']) {
                $data['new_values']['order_sent_contract_id'] = ManufacturerContract::find($data['new_values']['order_sent_contract_id'])->name;
            }
        }

        if (Arr::has($data, 'old_values.order_agreed_contract_id')) {
            if ($data['old_values']['order_agreed_contract_id']) {
                $data['old_values']['order_agreed_contract_id'] = ManufacturerContract::find($data['old_values']['order_agreed_contract_id'])->name;
            }
        }
        if (Arr::has($data, 'new_values.order_agreed_contract_id')) {
            if ($data['new_values']['order_agreed_contract_id']) {
                $data['new_values']['order_agreed_contract_id'] = ManufacturerContract::find($data['new_values']['order_agreed_contract_id'])->name;
            }
        }

        if (Arr::has($data, 'old_values.order_confirmed_contract_id')) {
            if ($data['old_values']['order_confirmed_contract_id']) {
                $data['old_values']['order_confirmed_contract_id'] = ManufacturerContract::find($data['old_values']['order_confirmed_contract_id'])->name;
            }
        }
        if (Arr::has($data, 'new_values.order_confirmed_contract_id')) {
            if ($data['new_values']['order_confirmed_contract_id']) {
                $data['new_values']['order_confirmed_contract_id'] = ManufacturerContract::find($data['new_values']['order_confirmed_contract_id'])->name;
            }
        }

        if (Arr::has($data, 'old_values.inner_contract_legal_entity_contract_id')) {
            if ($data['old_values']['inner_contract_legal_entity_contract_id']) {
                $data['old_values']['inner_contract_legal_entity_contract_id'] = LegalEntityContract::find($data['old_values']['inner_contract_legal_entity_contract_id'])->name;
            }
        }
        if (Arr::has($data, 'new_values.inner_contract_legal_entity_contract_id')) {
            if ($data['new_values']['inner_contract_legal_entity_contract_id']) {
                $data['new_values']['inner_contract_legal_entity_contract_id'] = LegalEntityContract::find($data['new_values']['inner_contract_legal_entity_contract_id'])->name;
            }
        }

        if (Arr::has($data, 'old_values.buyer_legal_entity_id')) {
            if ($data['old_values']['buyer_legal_entity_id']) {
                $data['old_values']['buyer_legal_entity_id'] = LegalEntity::find($data['old_values']['buyer_legal_entity_id'])->name;
            }
        }
        if (Arr::has($data, 'new_values.buyer_legal_entity_id')) {
            if ($data['new_values']['buyer_legal_entity_id']) {
                $data['new_values']['buyer_legal_entity_id'] = LegalEntity::find($data['new_values']['buyer_legal_entity_id'])->name;
            }
        }

        return $data;
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function orders()
    {
        return $this->hasMany(ManufacturerOrder::class);
    }

    public function payments()
    {
        return $this->hasMany(ManufacturerPayment::class);
    }

    public function commission_relations()
    {
        return $this->hasMany(ManufacturerCommissionRelation::class);
    }

    public function inner_payments()
    {
        return $this->hasMany(InnerPayment::class);
    }

    public function specifications()
    {
        return $this->hasMany(ManufacturerSpecification::class);
    }

    public function inner_specifications()
    {
        return $this->hasMany(InnerSpecification::class);
    }

    public function additional_documents()
    {
        return $this->hasMany(ProjectManufacturerAdditionalDocument::class);
    }

    public function additional_contracts()
    {
        return $this->hasMany(ManufacturerAdditionalContract::class);
    }

    public function generateTags(): array
    {
        return ['project:'.$this->project_id];
    }
}
