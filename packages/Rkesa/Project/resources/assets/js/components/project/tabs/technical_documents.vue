<style>
    .tec_docs td{
        vertical-align: middle;
    }
</style>

<template lang="pug">
    div
        div.project-section {{ $t('project.Technical_documentation') }}
        div.mb-2(style="border-top: 1px solid #dee2e6;")
            table.table.table-striped.tec_docs
                thead.text-center
                    tr
                        th(colspan="4" style="border-right: 1px solid #ccc; border-bottom: none; border-top: none;") {{ $t('project.For_contract_with_customer') }}
                        th(:colspan="project.manufacturers.length > 1 ? 3 : 2" style="border-right: 1px solid #ccc; border-bottom: none; border-top: none") {{ $t('project.For_contract_with_manufacturer') }}
                        th(colspan="6" style="border-bottom: none; border-top: none")
                    tr
                        th {{ $t('project.Document_name') }}
                        th {{ $t('project.Days_count_from_prepayment') }}
                        th {{ $t('project.Date') }}
                        th(style="border-right: 1px solid #ccc;") {{ $t('project.Document_language') }}
                        //
                        th(v-if="project.manufacturers.length > 1") {{ $t('project.Manufacturer') }}
                        th {{ $t('project.Days_count_from_prepayment') }}
                        th(style="border-right: 1px solid #ccc;") {{ $t('project.Date') }}
                        //
                        th {{ $t('project.Availability') }}
                        th {{ $t('project.Date_of_receiving') }}
                        th {{ $t('project.Document') }}
                        th {{ $t('project.Date_of_translation') }}
                        th {{ $t('project.Date_of_sending') }}
                        th {{ $t('project.Document') }}
                        th
                tbody
                    tr(v-for="doc in project.technical_documents")
                        td(style="width: 16%")
                            input.form-control(v-model="doc.name", :disabled="project.finished || !$root.can_with_project('update', 4)")
                        td(style="width: 7%")
                            input.form-control(v-model="doc.days_from_prepayment_customer" type="number", :disabled="project.finished || !$root.can_with_project('update', 4)")
                        td {{ customer_generate_date(doc) }}
                        td(style="border-right: 1px solid #ccc; width: 10%")
                            input.form-control(v-model="doc.document_language", :disabled="project.finished || !$root.can_with_project('update', 4)")
                        //
                        td(v-if="project.manufacturers.length > 1")
                            select.form-control(v-model="doc.manufacturer_id", :disabled="project.finished || !$root.can_with_project('update', 4)")
                                option(v-for="man in project.manufacturers", :value="man.manufacturer_id") {{ man.manufacturer.name }}
                        td(style="width: 7%;")
                            input.form-control(v-model="doc.days_from_prepayment_manufacturer" type="number", :disabled="project.finished || !$root.can_with_project('update', 4)")
                        td(style="border-right: 1px solid #ccc;") {{ manufacturer_generate_date(doc) }}
                        //
                        td
                            div(v-on:click="toogle_available(doc)" style="width: 80px;")
                                bootstrap-toggle(v-model="doc.available", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 4)")
                        td
                            date-picker(:lang="$root.locale" v-model="doc.receiving_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", format="DD.MM.YYYY", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 4)")
                        td
                            file-uploader(
                                :file_url="doc.orig_document_file"
                                :file_name="doc.orig_document_file_name"
                                :editable="!project.finished && $root.can_with_project('update', 4)"
                                @remove="remove_orig_document_file(doc)"
                                @finished="(arr) => { [doc.orig_document_file, doc.orig_document_file_name] = arr }")
                        td
                            date-picker(:lang="$root.locale" v-model="doc.translating_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", format="DD.MM.YYYY", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 4)")
                        td
                            date-picker(:lang="$root.locale" v-model="doc.sending_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", format="DD.MM.YYYY", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 4)")
                        td
                            file-uploader(
                                :file_url="doc.document_file"
                                :file_name="doc.document_file_name"
                                :editable="!project.finished && $root.can_with_project('update', 4)"
                                @remove="remove_document_file(doc)"
                                @finished="(arr) => { [doc.document_file, doc.document_file_name] = arr }")
                        td
                            button.btn.btn-danger(v-on:click="remove_doc(doc)", :disabled="project.finished || !$root.can_with_project('update', 4)") {{ $t('template.Remove') }}
            button.btn.btn-diga(v-on:click="add_doc", :disabled="project.finished || !$root.can_with_project('update', 4)") {{ $t('template.Add') }}
        div.row
            div.col-12.col-lg-6
                label {{ $t('project.Provisioning_terms') }}
                textarea.form-control(v-model="project.provisioning_terms", :disabled="project.finished || !$root.can_with_project('update', 4)")
            div.col-12.col-lg-6
                label {{ $t('project.Comment') }}
                textarea.form-control(v-model="project.comment_technical", :disabled="project.finished || !$root.can_with_project('update', 4)")
</template>

<script>
import moment from 'moment';

export default {
    props: {
        project: {
            type: Object,
        },
    },
    data: function(){
        return {
            //
        }
    },
    mounted(){
        if (this.project.technical_documents.length == 0){
            this.add_doc();
        }
    },
    methods: {
        toogle_available(doc){
            if (!doc.available && doc.receiving_date == null && !this.project.finished){
                doc.receiving_date = moment();
            }
        },
        customer_generate_date(doc){
            if (this.project.contract_payments[0].payment_date){
                return moment(this.project.contract_payments[0].payment_date).add(doc.days_from_prepayment_customer, 'days').format('DD.MM.YYYY');
            } else {
                return '';
            }
        },
        manufacturer_generate_date(doc){
            if (this.project.manufacturers.length > 1){
                if (doc.manufacturer_id) {
                    let m = this.project.manufacturers.find(e => e.manufacturer_id == doc.manufacturer_id);
                    if (m.payments[0] && m.payments[0].payment_date) {
                        return moment(m.payments[0].payment_date).add(doc.days_from_prepayment_manufacturer, 'days').format('DD.MM.YYYY');
                    } else {
                        return '';
                    }
                } else {
                    return '';
                }
            } else if (this.project.manufacturers[0].payments[0] && this.project.manufacturers[0].payments[0].payment_date) {
                return moment(this.project.manufacturers[0].payments[0].payment_date).add(doc.days_from_prepayment_manufacturer, 'days').format('DD.MM.YYYY');
            } else {
                return '';
            }
        },
        add_doc(){
            let newDoc = {
                id: 0,
                name: '',
                document_file: null,
                document_file_name: null,
                orig_document_file: null,
                orig_document_file_name: null,
                manufacturer_id: this.project.manufacturers[0].manufacturer_id,
            };
            this.project.technical_documents.push(newDoc);
        },
        remove_doc(doc){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))){
                if (!('removed_tech_docs' in this.project)){
                    this.project.removed_tech_docs = [];
                }
                this.project.removed_tech_docs.push(doc.id);
                let index = this.project.technical_documents.indexOf(doc);
                this.project.technical_documents.splice(index, 1);
            }
        },
        remove_document_file(doc){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                doc.document_file = null;
                doc.document_file_name = null;
            }
        },
        // orig
        remove_orig_document_file(doc){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                doc.orig_document_file = null;
                doc.orig_document_file_name = null;
            }
        },
    },
}
</script>