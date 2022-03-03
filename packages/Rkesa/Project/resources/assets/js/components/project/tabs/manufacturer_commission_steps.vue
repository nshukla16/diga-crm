<style>
    .contract_payment_steps td{
        vertical-align: middle;
    }
</style>

<template lang="pug">
    div.mb-2
        div.project-section
            span {{ $t('project.Commission_relationships') }}: {{ legal_entities_by_id[relation.legal_entity_id].name }}
            //span.clickable.ml-2(@click="remove_commission_relation()")
                i.fa.fa-times
        div.row.mb-2
            div.col-12.col-lg-6
                div.form-group.row
                    label.col-6.input-line {{ $t('project.Manufacturer') }}
                    label.col-6.input-line {{ manufacturer.manufacturer.name }}
                div.form-group.row
                    label.col-6.input-line {{ $t('project.Seller') }}
                    label.col-6.input-line {{ legal_entities_by_id[relation.legal_entity_id].name }}
                div.form-group.row
                    label.col-6.input-line {{ $t('project.Commission_price_to_pay') }}
                    div.col-6
                        div.row
                            div.col-8
                                vue-numeric.form-control(v-model="relation.commission_need_to_pay", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            div.col-4
                                select.form-control(v-model="relation.currency", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                    option(v-for="currency in $root.currencies", :value="currency.code") {{ currency.name + ' (' + currency.code + ')' }}
                div.fcol-6.input-line
                    label.w-100 {{ $t('project.Remainder') }}
                    h5 {{$root.formatFinanceValue(relation.commission_need_to_pay - (total_price )) + ' ' + relation.currency}}

            div.col-12.col-lg-6
                div.mb-2
                    h6 {{ $t('project.Comment') }}
                    textarea.form-control(v-model="relation.comment_commission", :disabled="project.finished || !$root.can_with_project('update', 1)")
                div.mb-2.d-flex(v-for="document in relation.documents")
                    input.form-control.mr-2(style="flex: 1;" type="text", v-model="document.name", :disabled="project.finished || !$root.can_with_project('update', 1)")
                    div.input-line.mr-2(style="flex: 1;")
                        file-uploader(
                            :file_url="document.file"
                            :file_name="document.file_name"
                            :editable="!project.finished && $root.can_with_project('update', 1)"
                            @remove="remove_document_file(document)"
                            @finished="(arr) => { [document.file, document.file_name] = arr }")
                    button.btn.btn-danger(v-on:click="remove_document(document)", :disabled="project.finished || !$root.can_with_project('update', 1)")
                        i.fa.fa-times
                button.btn.btn-diga(v-on:click="add_document", :disabled="project.finished || !$root.can_with_project('update', 1)") {{ $t('project.Add_document') }}
        div.row
            div.col-12
                table.contract_payment_steps.table.table-striped
                    thead
                        tr
                            th(style="width: 140px;") #
                            th(style="width: 100px;") %
                            //th(style="width: 100px;") {{ $t('project.Value') }}
                            th(style="width: 160px;") {{ $t('project.In') + ' ' + relation.currency }}
                            //th(style="width: 100px;") {{ $t('project.Currency') }}
                            //th(style="width: 130px;") {{ $t('project.In') + ' ' + $root.global_settings.currency }}
                            th(style="width: 160px;" v-if="relation.currency != $root.global_settings.currency") {{ $t('project.In') + ' ' + $root.global_settings.currency }}
                            th {{ $t('project.Invoice') }}
                            th {{ $t('project.Accounting_statement') }}
                            th {{ $t('project.Payment_date') }}
                            th {{ $t('template.Remove') }}
                    tbody
                        tr(v-for="(payment,i) in relation.commission_payments")
                            td {{ i == 0 ? $t('project.Prepayment') : $t('project.Payment') }}
                            td {{ relation.commission_need_to_pay != 0 ? round10(payment.price*100/relation.commission_need_to_pay) : '0' }}
                            td
                                vue-numeric.form-control(v-model="payment.price", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            //td
                                select.form-control(v-model="payment.currency", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                    option(v-for="currency in $root.currencies", :value="currency.code") {{ currency.code }}
                            td(v-if="relation.currency != $root.global_settings.currency")
                                vue-numeric.form-control(v-model="payment.in_main_currency", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            td
                                file-uploader(
                                    :file_url="payment.invoice_file"
                                    :file_name="payment.invoice_file_name"
                                    :editable="!project.finished && $root.can_with_project('update', 1)"
                                    @remove="remove_invoice_file(payment)"
                                    @finished="(arr) => { [payment.invoice_file, payment.invoice_file_name] = arr }")
                            td
                                file-uploader(
                                    :file_url="payment.accounting_statement_file"
                                    :file_name="payment.accounting_statement_file_name"
                                    :editable="!project.finished && $root.can_with_project('update', 1)"
                                    @remove="remove_accounting_statement_file(payment)"
                                    @finished="(arr) => { [payment.accounting_statement_file, payment.accounting_statement_file_name] = arr }")
                            td
                                date-picker(:disabled="!payment.accounting_statement_file || project.finished || !$root.can_with_project('update', 1)" format="DD.MM.YYYY" v-model="payment.payment_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", :lang="$root.locale", :width="'100%'")
                            td.column_autosize
                                button.btn(v-if="i != 0" v-on:click="remove_step(payment)", :disabled="project.finished || !$root.can_with_project('update', 1)") {{ $t('template.Remove') }}
                    tfoot
                        tr
                            td(style="white-space: nowrap;")
                                span {{ $t('project.Total_paid') }}
                                popper.ml-1(:append-to-body="true")
                                    i.fa.fa-question-circle-o(slot="reference")
                                    div.popper {{ $t('project.Summarized_fields_with_payment_date') }}
                            td
                            td {{ total_price }}
                            td(v-if="relation.currency != $root.global_settings.currency") {{ total_price_in_main_currency }}
                            td(colspan="4")
                button.btn.btn-diga(v-on:click="add_step", :disabled="project.finished || !$root.can_with_project('update', 1)") {{ $t('template.Add') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: {
        project: Object,
        manufacturer: Object,
        relation: Object,
    },
    data: function() {
        return {
            //
        }
    },
    computed: {
        ...mapGetters({
            legal_entities: 'getLegalEntities',
            legal_entities_by_id: 'getLegalEntitiesById',
        }),
        total_price(){
            let total_price = this.relation.commission_payments.map(p => p.payment_date != null ? parseFloat(p.price) : 0).reduce((a, b) => a + b, 0).toFixed(2);
            return total_price;
        },
        total_price_in_main_currency(){
            let total_price = this.relation.commission_payments.map(p => p.payment_date != null ? parseFloat(p.in_main_currency) : 0).reduce((a, b) => a + b, 0).toFixed(2);
            //                this.project.contract_total_price = total_price;
            return total_price;
        },
    },
    created(){
        if (this.relation.commission_payments.length == 0){ // if it is creating of new project, add prepayment field
            this.add_step();
        }
    },
    methods: {
        add_document(){
            let newDoc = {
                id: 0,
                name: '',
                file: null,
                file_name: null,
            };
            this.relation.documents.push(newDoc);
        },
        remove_document_file(document){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                document.file = null;
                document.file_name = null;
            }
        },
        remove_document(document){
            if (confirm(this.$root.$t("project.Sure_remove_document"))){
                if (!('removed_documents' in this.relation)){
                    this.relation.removed_documents = [];
                }
                this.relation.removed_documents.push(document.id);
                let index = this.relation.documents.indexOf(document);
                this.relation.documents.splice(index, 1);
            }
        },
        round10: function (num){
            return this.$root.roundNumber(num, 2);
        },
        remove_accounting_statement_file(payment){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                payment.accounting_statement_file = null;
                payment.accounting_statement_file_name = null;
            }
        },
        remove_invoice_file(payment){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                payment.invoice_file = null;
                payment.invoice_file_name = null;
            }
        },
        add_step(){
            let contractPayment = {
                id: 0,
                percent: 0,
                price: 0,
                currency: this.$root.global_settings.currency,
                invoice_file: null,
                invoice_file_name: null,
                accounting_statement_file: null,
                accounting_statement_file_name: null,
                payment_date: null,
                in_main_currency: 0,
            };
            this.relation.commission_payments.push(contractPayment);
        },
        remove_step(payment){
            if (confirm(this.$root.$t("project.Sure_remove_step"))){
                if (!('removed_commission_steps' in this.relation)){
                    this.relation.removed_commission_steps = [];
                }
                this.relation.removed_commission_steps.push(payment.id);
                let index = this.relation.commission_payments.indexOf(payment);
                this.relation.commission_payments.splice(index, 1);
            }
        },
    },
}
</script>
