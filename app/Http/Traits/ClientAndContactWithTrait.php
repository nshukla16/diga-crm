<?php

namespace App\Http\Traits;

trait ClientAndContactWithTrait
{
    static $client_with = array(
        'client_contacts.events', 'client_contacts.events', 'client_referrer',
        'client_contacts.client_contact_phones', 'client_contacts.client_contact_emails',
        // services
        // 'client_contacts.services', 'client_contacts.services.service_type', 'client_contacts.services.service_state',
        // 'client_contacts.services.attachments', 'client_contacts.services.service_priority',
        // // checklists
        // 'client_contacts.services.checklist_filleds', 'client_contacts.services.checklist_filleds.checklist',
        // history
        // 'client_contacts.client_history', 'client_contacts.client_history.service', 'client_contacts.client_history.event.event_type',
        // 'client_contacts.client_history.service_attachment', 'client_contacts.client_history.client_contact',
        // projects
        'projects', 'equipment', 'equipment.manufacturer',
        'connection'
    );

    static $contact_with = array(
        'services', 'client_referrer', 'events',
        // history

        // 'client_history', 'client_history.service',
        // 'client_history.service_attachment', 'client_history.event.event_type', 'client_history.client_contact', 'client_history.call',

        // services
        // 'services.attachments', 'services.service_priority', 'services.service_type', 'services.service_state',
        // // checklists
        // 'services.checklist_filleds', 'services.checklist_filleds.checklist',
        //
        'client_contact_phones', 'client_contact_emails',
        'events', 'client.client_contacts', 'client.client_contacts.client_contact_phones',
        // projects
        'client.projects', 'client.equipment', 'client.equipment.manufacturer'
    );

    private function format_attributes($attrs)
    {
        if ($attrs) {
            $ad_fields = $attrs;
            foreach ($ad_fields as $key => $value) {
                unset($ad_fields[$key]['name']);
                unset($ad_fields[$key]['options']);
                unset($ad_fields[$key]['value_calculated']);
                unset($ad_fields[$key]['show_in_card']);
            }
            return $ad_fields;
        } else {
            return array();
        }
    }
}
