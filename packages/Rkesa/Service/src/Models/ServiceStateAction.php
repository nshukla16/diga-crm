<?php

namespace Rkesa\Service\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceStateAction extends Model
{
    protected $fillable = ['order', 'type',
        'email_type', 'email_address', 'email_subject', 'email_text', 'email_filename', 'email_include_estimate_type',
        'email_include_resource_attachments', 'email_cc', 'email_include_checklist_id', 'email_show',
        'sms_text', 'sms_type', 'sms_to',
        'event_type_id', 'event_user_id', 'event_date_type', 'event_description', 'event_description_not_editable',
        'checklist_id', 'path', 'url'];
}
