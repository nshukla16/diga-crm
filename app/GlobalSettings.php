<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class GlobalSettings extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'site_name', 'unlocker_user_id', 'responsible_user_id',
        'last_estimate_number', 'company_type', 'estimate_bottom_text', 'estimate_conditions_text',
        'estimate_show_contract', 'new_service_state_id', 'add_service_state_id', 'new_event_type_id',
        'color1', 'color2', 'color3', 'color4', 'color5', 'head', 'incoming_sms', 'incoming_sms_text',
        'incoming_mail', 'incoming_mail_subject', 'incoming_mail_text', 'incoming_mail_address',
        'estimate_orientation', 'planning_orientation', 'default_language',
        'fb_page_marker',
        'gd_enabled',

        'zadarma_enabled', 'zadarma_key', 'zadarma_secret', 'zadarma_redirect_to_responsible', 'zadarma_new_task_if_no_answer', 'zadarma_task_type_id', 'zadarma_missed_call_responsible_id',

        'checkfront_enabled', 'checkfront_host', 'checkfront_api_key', 'checkfront_api_secret', 'currency',
        'enable_companies', 'first_day_of_week',
        'use_default_smtp',
        'mailchimp_integration_enabled', 'mailchimp_api_key',
        'default_planning_roadmap_id', 'user_planning_service_state_id', 'user_planning_user_id',
        'invoice_increment',
        'invoice_auto_send_email',
        'invoice_auto_send_email_password',

        'telegram_enabled', 'tg_api_id', 'tg_api_hash', 'tg_phone', 'vacation_days_per_year',

        'disable_service_address', 'enable_total_by_day_in_calendar', 'enable_service_name_in_event_in_calendar',
        'invoice_notes', 'vat_exemption_reason_id', 'always_use_exemption_for_invoices',
        'use_special_regime_of_iva_in_estimates',

        'google_ads_enabled', 'google_ads_developer_token', 'google_ads_client_customer_id', 'google_ads_user_agent', 'google_ads_client_id', 'google_ads_client_secret', 'google_ads_refresh_token',

        'cx_enabled', 'contractor_service_state_id'
    ];

    protected $casts = [
        'checkfront_enabled' => 'boolean',
        'fb_enabled' => 'boolean',
        'gd_enabled' => 'boolean',
        'zadarma_enabled' => 'boolean',
        'enable_companies' => 'boolean',
        'use_default_smtp' => 'boolean',
        'client_attributes' => 'array',
        'contact_attributes' => 'array',
        'move_events_to_next_day' => 'boolean',
        'mailchimp_integration_enabled' => 'boolean',
        'telegram_enabled' => 'boolean',
        'disable_service_address' => 'boolean',
        'enable_total_by_day_in_calendar' => 'boolean',
        'enable_service_name_in_event_in_calendar' => 'boolean',
        'always_use_exemption_for_invoices' => 'boolean',
        'use_special_regime_of_iva_in_estimates' => 'boolean',
        'google_ads_enabled' => 'boolean',
        'cx_enabled' => 'boolean',
    ];

    protected $appends = ['modules', 'settings'];

    public function getModulesAttribute()
    {
        return config('modules_enabled');
    }

    public function getSettingsAttribute()
    {
        return \App\Setting::associative_array();
    }
}
