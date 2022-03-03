<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;

class LegalEntity extends Model
{
    protected $fillable = [
        'name',
        'tax_number',
        'address',
        'kpp_number',
        'bank_name',
        'bic',
        'bank_receiver_number',
        'bank_account_number',
        'logo_file_name',
        'logo_file_path',
        'sign_file_name',
        'sign_file_path',
        'footer_file_name',
        'footer_file_path',
        'tax_enabled',
        'tax_rate',
        'commissioning_certificate_template_file_path',
        'commissioning_certificate_template_file_name',
        'ready_notification_template_file_path',
        'ready_notification_template_file_name',
        'commissioning_experience_certificate_template_file_path',
        'commissioning_experience_certificate_template_file_name',
        'last_logistic_order_number',
        'logistic_order_number_format'
    ];

    public function contracts()
    {
        return $this->hasMany(LegalEntityContract::class);
    }
}
