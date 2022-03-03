<?php

namespace Rkesa\Project\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Rkesa\Client\Models\Client;
use Rkesa\Service\Models\Service;
use Illuminate\Support\Facades\Log;
use Rkesa\Project\Models\SidePayment;
use Rkesa\Project\Models\AdditionalExpense;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Project extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name', 'project_type_id', 'contract_number', 'contract_file', 'contract_filename', 'contract_type',
        'phased_deliveries', 'specification_number', 'specification_file', 'specification_filename', 'conditions_of_delivery',
        'destination', 'date_of_sign_contract', 'contract_price', 'contract_currency', 'contract_currency_type',
        'seller_legal_entity_id', 'client_id', 'lessee_client_id', 'service_id',
        // Contract payment steps
        'comment_contract_payments',
        // Limits
        'limit_type', 'limit_forming_type', 'limit_forming_date', 'limit_forming_days', 'limit_before_date', 'comment_limits',
        // Common documents
        'ready_notification', 'ready_notification_date', 'ready_notification_file_path', 'ready_notification_file_name',
        'acceptance_certificate', 'acceptance_certificate_date', 'acceptance_certificate_file_path', 'acceptance_certificate_file_name',
        'shipping_documents_sent', 'shipping_documents_sent_date', 'shipping_documents_sent_file_path', 'shipping_documents_sent_file_name',
        'shipping_documents_received', 'shipping_documents_received_date', 'shipping_documents_received_file_path', 'shipping_documents_received_file_name',
        'comment_documents',
        // Logistic
        'comment_logistic', 'transportation_total', 'transportation_vat_total',
        // Technical
        'comment_technical', 'provisioning_terms',
        // Installation
        "installation_duration",
        "payment_installation_comment", "direct_expenses",
        "transportation_expenses", "airline_tickets_expenses", "food_expenses", "accommodation_expenses", "equipment_ex_certificate", 'installation_total_price',
        //
        "responsible_user_id", "finished", "contract_total_price",
        "logistics_enabled", "project_status_id", 'finished_at',
        "vat_difference",

        'calculation_of_direct_costs_file', 'calculation_of_direct_costs_file_name',
        'commercial_offer_file', 'commercial_offer_file_name',
        'offer_drawing_file', 'offer_drawing_file_name'
    ];

    protected $casts = [
        'ready_notification' => 'boolean',
        'acceptance_certificate' => 'boolean',
        'shipping_documents_sent' => 'boolean',
        'shipping_documents_received' => 'boolean',
        'finished' => 'boolean',
        'logistics_enabled' => 'boolean',
    ];

    protected $auditExclude = [
        'ready_notification_file_path', 'acceptance_certificate_file_path', 'acceptance_certificate_file_name', 'shipping_documents_sent_file_path',
        'shipping_documents_sent_file_name', 'shipping_documents_received_file_name', 'shipping_documents_received_file_path',
        'contract_file', 'id', 'specification_file'
    ];

    protected $appends = ['delivery_terms'];

    public function transformAudit(array $data): array
    {
        if (
            Arr::has($data, 'new_values.contract_total_price') &&
            Arr::has($data, 'old_values.contract_total_price')
        ) {
            if ($data['old_values']['contract_total_price'] == $data['new_values']['contract_total_price']) {
                unset($data['old_values']['contract_total_price']);
                unset($data['new_values']['contract_total_price']);
            }
        }
        if (
            Arr::has($data, 'new_values.installation_total_price') &&
            Arr::has($data, 'old_values.installation_total_price')
        ) {
            if ($data['old_values']['installation_total_price'] == $data['new_values']['installation_total_price']) {
                unset($data['old_values']['installation_total_price']);
                unset($data['new_values']['installation_total_price']);
            }
        }
        if (
            Arr::has($data, 'new_values.transportation_total') &&
            Arr::has($data, 'old_values.transportation_total')
        ) {
            if ($data['old_values']['transportation_total'] == $data['new_values']['transportation_total']) {
                unset($data['old_values']['transportation_total']);
                unset($data['new_values']['transportation_total']);
            }
        }

        // Prefill dependencies names

        if (Arr::has($data, 'old_values.project_type_id')) {
            $data['old_values']['project_type_id'] = ProjectType::find($data['old_values']['project_type_id'])->name;
        }
        if (Arr::has($data, 'new_values.project_type_id')) {
            $data['new_values']['project_type_id'] = ProjectType::find($data['new_values']['project_type_id'])->name;
        }

        if (Arr::has($data, 'old_values.project_status_id')) {
            $data['old_values']['project_status_id'] = ProjectStatus::find($data['old_values']['project_status_id'])->name;
        }
        if (Arr::has($data, 'new_values.project_status_id')) {
            $data['new_values']['project_status_id'] = ProjectStatus::find($data['new_values']['project_status_id'])->name;
        }

        if (Arr::has($data, 'old_values.lessee_client_id')) {
            if ($data['old_values']['lessee_client_id']) {
                $data['old_values']['lessee_client_id'] = Client::find($data['old_values']['lessee_client_id'])->name;
            }
        }
        if (Arr::has($data, 'new_values.lessee_client_id')) {
            if ($data['new_values']['lessee_client_id']) {
                $data['new_values']['lessee_client_id'] = Client::find($data['new_values']['lessee_client_id'])->name;
            }
        }

        if (Arr::has($data, 'old_values.client_id')) {
            $data['old_values']['client_id'] = Client::find($data['old_values']['client_id'])->name;
        }
        if (Arr::has($data, 'new_values.client_id')) {
            $data['new_values']['client_id'] = Client::find($data['new_values']['client_id'])->name;
        }

        if (Arr::has($data, 'old_values.responsible_user_id')) { // dont have if condition because it is required field
            $data['old_values']['responsible_user_id'] = User::find($data['old_values']['responsible_user_id'])->name;
        }
        if (Arr::has($data, 'new_values.responsible_user_id')) {
            $data['new_values']['responsible_user_id'] = User::find($data['new_values']['responsible_user_id'])->name;
        }

        if (Arr::has($data, 'old_values.service_id')) {
            if ($data['old_values']['service_id']) {
                $data['old_values']['service_id'] = Service::find($data['old_values']['service_id'])->get_service_number();
            }
        }
        if (Arr::has($data, 'new_values.service_id')) {
            if ($data['new_values']['service_id']) {
                $data['new_values']['service_id'] = Service::find($data['new_values']['service_id'])->get_service_number();
            }
        }

        if (Arr::has($data, 'old_values.seller_legal_entity_id')) {
            $data['old_values']['seller_legal_entity_id'] = LegalEntity::find($data['old_values']['seller_legal_entity_id'])->name;
        }
        if (Arr::has($data, 'new_values.seller_legal_entity_id')) {
            $data['new_values']['seller_legal_entity_id'] = LegalEntity::find($data['new_values']['seller_legal_entity_id'])->name;
        }

        return $data;
    }

    public function GetNameWithLinkAttribute()
    {
        return '<a href="/projects/' . $this->id . '">' . $this->name . '</a>';
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lessee_client()
    {
        return $this->belongsTo(Client::class, 'lessee_client_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function project_type()
    {
        return $this->belongsTo(ProjectType::class);
    }

    public function specifications()
    {
        return $this->hasMany(ProjectSpecification::class);
    }

    public function contract_payments()
    {
        return $this->hasMany(ContractPayment::class);
    }

    public function manufacturers()
    {
        return $this->hasMany(ProjectManufacturer::class);
    }

    public function technical_documents()
    {
        return $this->hasMany(TechnicalDocument::class);
    }

    public function installation_expense_payments()
    {
        return $this->hasMany(InstallationPaymentStep::class);
    }

    public function installation_documents()
    {
        return $this->hasMany(InstallationDocument::class);
    }

    public function additional_documents()
    {
        return $this->hasMany(AdditionalDocument::class);
    }

    public function seller_legal_entity()
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function responsible_user()
    {
        return $this->belongsTo(User::class);
    }

    public function additional_contracts()
    {
        return $this->hasMany(ProjectAdditionalContract::class);
    }

    public function side_payments()
    {
        return $this->hasMany(SidePayment::class);
    }

    public function additional_expenses()
    {
        return $this->hasMany(AdditionalExpense::class);
    }

    public function fact_delivery_entities()
    {
        return $this->hasMany(ProjectFactDeliveryEntity::class);
    }

    public function generateTags(): array
    {
        return ['project:' . $this->id];
    }

    public function getDeliveryTermsAttribute()
    {
        return $this->delivery_terms();
    }

    public function delivery_terms()
    {
        $p_manufacturers = $this->manufacturers();
        if ($p_manufacturers->count() == 0) {
            return null;
        }
        $p_manufacturer = $p_manufacturers->first();

        if ($p_manufacturer->limit_forming_type == 0) {
            $date_type = '';
            if ($p_manufacturer->limit_forming_date == 0) {
                $date_type = trans('project.before_date_of_prepayment');
            } else {
                $date_type = trans('project.before_date_of_order_confirmation');
            }
            return $p_manufacturer->limit_forming_days . '' . trans('project.days_before') . '' . $date_type;
        } else {
            return Carbon::parse($p_manufacturer->limit_before_date)->format('d.m.y');
        }
    }
}
