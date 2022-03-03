import moment from 'moment';
import { project_conditions_of_delivery, manufacturer_conditions_of_delivery, inner_conditions_of_delivery, shipping_order_conditions_of_delivery } from '../../../packages/Rkesa/Project/resources/assets/js/helper'

const AuditTranslation = {
    name: "project.Name",
    contract_total_price: "project.Total_price_to_pay",
    size: "project.Size",
    model: "project.Model",
    vendor_code: "project.Vendor_code",
    count: "project.Count",
    transportation_total: "project.Transportation_total",
    installation_total_price: "project.Installation_total_price",
    days_from_prepayment_customer: "project.Days_count_from_prepayment",
    document_language: "project.Document_language",
    contract_currency: "project.Currency",
    currency: "project.Currency",
    receiving_date: "project.Date_of_receiving",
    translating_date: "project.Date_of_translation",
    sending_date: "project.Date_of_sending",
    payments_total_price: "project.Payments_total_price",
    inner_contract_currency: "project.Inner_contract_currency",
    order_date: "project.Order_date",
    payment_name: "project.Name_of_payment",
    payment_value: "project.Payment_value",
    payment_main_currency: "project.Currency",
    payment_invoice_sent: "project.Invoice_sent",
    payment_date: "project.Payment_date",
    payment_confirmed: "project.Confirmed",
    contract_number: "project.Contract_number",
    available: "project.Available",
    percent: "project.Percent",
    price: "project.Price",
    invoice_sent: "project.Invoice_sent",
    in_main_currency: "project.In_main_currency",
    invoice_sent_at: "project.Invoice_sent_at",
    limit_forming_days: "project.Limit_forming_days",
    comment_limits: "project.Comment_limits",
    notes: "project.Notes",
    ready_notification: "project.Ready_notification",
    ready_notification_date: "project.Ready_notification_date",
    shipping_documents_received: "project.Shipping_documents_received",
    shipping_documents_received_date: "project.Shipping_documents_received_date",
    acceptance_certificate: "project.Acceptance_certificate",
    acceptance_certificate_date: "project.Acceptance_certificate_date",
    shipping_documents_sent: "project.Shipping_documents_sent",
    shipping_documents_sent_date: "project.Shipping_documents_sent_date",
    limit_forming_date: "project.Limit_forming_date",
    number: "project.Number",
    signed_date: "project.Specification_signed_date",
    document_name: "project.Name",
    confirmed: "project.Confirmed",
    confirmed_date: "project.Confirmed_date",
    payment_confirmed_date: "project.Confirmed_date",
    warranty_period: "project.Warranty_period",
    warranty_expiration_date: "project.Warranty_expiration_date",
    installation_duration: "project.Installation_duration",
    direct_expenses: "project.Direct_expenses",
    transportation_expenses: "project.Transportation_expenses",
    airline_tickets_expenses: "project.Airline_tickets",
    equipment_ex_certificate: "project.Equipment_commissioning_experience_certificate_filled",
    equipment_certificate: "project.Equipment_commissioning_certificate",
    equipment_certificate_date: "project.Equipment_certificate_date",
    equipment_ex_certificate_date: "project.Equipment_ex_certificate_date",
    initial_date: "project.Initial_date",
    destination: "project.Destination_point",
    contract_price: "project.Price_of_the_contract",
    logistics_enabled: "project.Logistics",
    specification_filename: "project.File_of_specification",
    phased_deliveries: "project.Phased_deliveries",
    conditions_of_delivery: "project.Conditions_of_delivery",
    ready_notification_file_name: "project.Ready_notification",
    limit_before_date: "project.Limit_before_date",
    exist: "project.Exist",
    document_date: "project.Date",
    comment_contract_payments: "project.Comment_contract_payments",
    order_number: "project.Order_number",
    order_agreed: "project.Order_agreed_with_tech_doc",
    order_agreed_at: "project.Order_agreed_at",
    order_sent: "project.Order_sent",
    order_confirmed: "project.Order_confirmed",
    order_sent_at: "project.Order_sent_at",
    order_confirmed_at: "project.Order_confirmed_at",
    comment_documents: "project.Comment_documents",
    comment_main: "project.Comment_main",
    from_db: "project.From_db",
    designated_shipping_date: "project.Designated_shipping_date",
    fact_shipping_date: "project.Fact_shipping_date",
    comment_preparation_steps: "project.Comment_preparation_steps",
    comment_manufacturer_payments: "project.Comment_manufacturer_payments",
    comment_inner_payments: "project.Comment_inner_payments",
    inner_contract_number: "project.Inner_contract_number",
    need_to_pay: "project.Need_to_pay",
    inner_need_to_pay: "project.Inner_need_to_pay",
    delivery_conditions: "project.Conditions_of_delivery",
    sender_legal_address: "project.Sender_legal_address",
    loading_ready_date: "project.Loading_ready_date",
    order_contract_and_specifications: "project.Sender_contract_number",
    inner_specification_number: "project.Inner_order_name",
    client_contract_number: "project.Client_contract_number",
    uploading_address: "project.Uploading_address",
    manufacturer_legal_address: "project.Manufacturer_legal_address",
    loading_selling_price: "project.Loading_selling_price",
    manufacturer_name: "project.Manufacturer_name",
    manufacturer_contact_name: "project.Manufacturer_contact_name",
    manufacturer_contact_phone: "project.Manufacturer_contact_phone",
    manufacturer_contact_email: "project.Manufacturer_contact_email",
    shipment_place: "project.Shipment_place",
    additional_loading: "project.Additional_loading",
    destination_place: "project.Destination_place",
    shipment_type_and_counts: "project.Shipment_type_and_counts",
    consignment_receiver_company_name: "project.Consignment_receiver_company_name",
    consignment_receiver_address: "project.Consignment_receiver_address",
    consignment_receiver_phone: "project.Consignment_receiver_phone",
    final_buyer: "project.Final_buyer",
    downloading_address: "project.Downloading_address",
    loading_name: "project.Loading_name",
    loading_cost_price: "project.Loading_cost_price",
    text: "project.Text",
    date: "project.Date",
    comment_carrier: "project.Comment_carrier",
    customs_application: "project.Customs_documents",
    customs_application_date: "project.Date_of_application",
    customs_issue: "project.Customs_issue",
    customs_issue_date: "project.Date_of_issue",
    approximate_date_of_arrival: "project.Approximate_arrival_date",
    approximate_date_of_arrival_to_temporary: "project.Approximate_arrival_date_to_temporary",
    fact_delivery: "project.Fact_delivery",
    fact_delivery_date: "project.Fact_delivery_date",
    days_from_prepayment_manufacturer: "project.Days_count_from_prepayment",
    provisioning_terms: "project.Provisioning_terms",
    comment_technical: "project.Comment_technical",
    food_expenses: "project.Meal",
    payment_currency: "project.Currency",
    date_of_sign_contract: "project.Date_of_signing_contract",
    contract_signed_date: "project.Date_of_signing_contract",
    dt: "project.Dt",
    limit_type: "project.Type_of_limits",
    limit_forming_type: "project.Date_forming_type",
    contract_filename: "project.Contract_file",
    specification_number: "project.Number_of_specification",
    project_type_id: "project.Type_of_project",
    project_status_id: "project.Project_status",
    lessee_client_id: "project.Lessee",
    client_id: "project.Buyer",
    responsible_user_id: "project.Responsible",
    contract_type: "project.Type_of_contract",
    service_id: "project.Service_number",
    seller_legal_entity_id: "project.Seller",
    contract_currency_type: "project.Type_of_payment",
    contract_file_name: "project.Additional_contract_file",
    estimate_unit_id: "project.Measure",
    order_sent_file_name: "project.Order_sent_file",
    order_confirmed_file_name: "project.Order_confirmed_file",
    order_agreed_file_name: "project.Order_agreed_file",
    order_sent_contract_id: "project.Order_sent_file",
    order_agreed_contract_id: "project.Order_agreed_file",
    order_confirmed_contract_id: "project.Order_confirmed_file",
    manufacturer_contract_id: "project.Document",
    legal_entity_contract_id: "project.Specification_number",
    buyer_legal_entity_id: "project.Buyer",
    document_file_name: "project.Document",
    carrier_id: "project.Carrier_expiditor",
    carrier_contract_id: "project.Agreement",
    invoice_file_name: "project.Invoice",
    accounting_statement_file_name: "project.Payment_order",
    orig_document_file_name: "project.Original_document",
    payment_installation_comment: "project.Installation_comment",
    accommodation_expenses: "project.Accommodation",
    equipment_certificate_file_name: "project.Equipment_commissioning_certificate",
    equipment_ex_certificate_file_name: "project.Equipment_commissioning_experience_certificate",
    payment_accounting_file_name: "project.Accounting_statement",
    payment_invoice_file_name: "project.Invoice",
    legal_entity_id: "project.Legal_entity",
    project_manufacturer_id: "project.Manufacturer",
    comment_commission: "project.Commission_comment",
    commission_need_to_pay: "project.Commission_price_to_pay",
    file_name: "project.Document",
    finished: "project.Finished",
    finished_at: "project.Finished_at",
};

function customValue($this, key, val, auditable_type){
    // i18n nulls and boolean
    if (val === true){
        val = $this.$t("template.Yes");
    } else if (val === false){
        val = $this.$t("template.No");
    } else if (val === null){
        val = $this.$t("template.No");
    }

    // i18n booleans that we receive as 0 and 1
    let boolean_keys_to_translate = [
        'ready_notification', 'shipping_documents_sent', 'shipping_documents_received',
        'acceptance_certificate', 'order_agreed', 'order_sent', 'order_confirmed', 'phased_deliveries', 'confirmed',
        'customs_application', 'customs_issue', 'fact_delivery', 'finished',
    ];

    if (boolean_keys_to_translate.indexOf(key) !== -1){
        if ([0, 1].indexOf(val) !== -1) {
            val = [$this.$t('project.No'), $this.$t('project.Yes')][val];
        }
    }

    let docs = ['order_sent_contract_id', 'order_agreed_contract_id', 'order_confirmed_contract_id', 'inner_contract_legal_entity_contract_id'];

    if (docs.indexOf(key) !== -1){
        if (val === 0){
            val = $this.$t('project.Not_selected');
        }
    }

    // i18n custom strings
    if (key === 'contract_type') {
        val = val === 0 ? $this.$t('project.With_transition') : $this.$t('project.Direct');
    }

    if (key === 'contract_currency_type') {
        val = val === 0 ? $this.$t('project.In_currency') : $this.$t('project.In_main_currency') + ' ' + $this.$root.current_currency.code;
    }

    if (key === 'limit_type'){
        val = val === 0 ? $this.$t('project.Limit_for_supplying_transporting') : $this.$t('project.Limit_for_shipment');
    }

    if (key === 'limit_forming_type'){
        val = val === 0 ? $this.$t('project.Amount_days') : $this.$t('project.Before_date');
    }

    if (key === 'direct_expenses' && [0, 1, 2].indexOf(val) !== -1){
        val = [$this.$t('project.Customer'), $this.$t('project.Provider'), $this.$t('project.Apart')][val];
    }

    if (key === 'airline_tickets_expenses' && [0, 1].indexOf(val) !== -1){
        val = val === 0 ? $this.$t('project.Customer') : $this.$t('project.Provider');
    }

    if (key === 'transportation_expenses' && [0, 1].indexOf(val) !== -1){
        val = val === 0 ? $this.$t('project.Customer') : $this.$t('project.Provider');
    }

    if (key === 'food_expenses' && [0, 1].indexOf(val) !== -1){
        val = val === 0 ? $this.$t('project.Customer') : $this.$t('project.Provider');
    }

    if (key === 'accommodation_expenses' && [0, 1].indexOf(val) !== -1){
        val = val === 0 ? $this.$t('project.Customer') : $this.$t('project.Provider');
    }

    if (auditable_type === 'Rkesa\\Project\\Models\\ProjectManufacturer' && key === 'limit_forming_type'){
        key = $this.$t('project.Shipping_date_forming_type');
    }

    if (auditable_type === 'Rkesa\\Project\\Models\\SpecificationAdditionalContract' && key === 'legal_entity_contract_id'){
        key = $this.$t('project.Additional_contract_number');
    }

    if (auditable_type === 'Rkesa\\Project\\Models\\ManufacturerOrderManager' && key === 'user_id'){
        key = $this.$t('project.Manager');
    }

    if (key === 'inner_contract_legal_entity_contract_id'){
        key = $this.$t('project.Inner_contract_number');
    }

    if (key === 'limit_forming_date'){
        val = val === 0 ? $this.$t('project.Date_of_prepayment') : $this.$t('project.Date_of_order_confirming');
    }

    if (auditable_type === 'Rkesa\\Project\\Models\\ProjectManufacturer' && key === 'limit_forming_date'){
        key = $this.$t('project.Shipping_limit_forming_date');
    }

    if (auditable_type === 'Rkesa\\Project\\Models\\Project' && key === 'conditions_of_delivery'){
        val = project_conditions_of_delivery[val];
    }

    if (auditable_type === 'Rkesa\\Project\\Models\\ProjectManufacturer' && key === 'conditions_of_delivery'){
        val = manufacturer_conditions_of_delivery[val];
    }

    if (auditable_type === 'Rkesa\\Project\\Models\\InnerSpecification' && key === 'delivery_conditions'){
        val = inner_conditions_of_delivery[val];
    }

    if (auditable_type === 'Rkesa\\Project\\Models\\ManufacturerOrder' && key === 'conditions_of_delivery'){
        val = shipping_order_conditions_of_delivery[val];
    }

    if (auditable_type === 'Rkesa\\Project\\Models\\ContractPayment' && key === 'invoice_sent'){
        // we check if it is really numbers (because they can be booleans, in this case we dont need to i18n them)
        if ([0, 1].indexOf(val) !== -1) {
            val = [$this.$t('project.No'), $this.$t('project.Yes')][val];
        }
    }

    // i18n dates
    let formats = [
        moment.ISO_8601,
        "YYYY-MM-DD",
    ];
    let dt = moment(val, formats, true);
    if (dt._isValid && typeof (val) === 'string') {
        val = dt.format('DD.MM.YYYY');
    }

    // format output
    key = $this.$t(AuditTranslation[key]) || key;

    return key.concat(' - ' + val);
}
export {customValue};
