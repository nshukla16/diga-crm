<style>
    .contract_payment_steps td{
        vertical-align: middle;
    }
</style>

<template lang="pug">
    div.mb-2
        div.project-section {{ $t('project.Transportation_expenses_block') }}
        table.contract_payment_steps.table.table-striped
            thead
                tr
                    th(style="width: 300px;") {{ $t('project.Purpose_of_payment') }}
                    th(style="width: 100px;") {{ $t('project.Value') }}
                    th(style="width: 100px;") {{ $t('project.Currency') }}
                    th(style="width: 130px;") {{ $t('project.In') + ' ' + $root.global_settings.currency }}
                    th {{ $t('project.Invoice') }}
                    th {{ $t('project.Payment_order') }}
                    th {{ $t('project.Payment_date') }}
                    th(style="width: 260px;") {{ $t('project.Confirmed') }}
                    th {{ $t('project.Document') }}
                    th {{ $t('template.Remove') }}
            tbody
                tr(v-for="(payment,i) in order.transportation_payments")
                    td
                        input.form-control(v-model="payment.name", :disabled="project.finished || !$root.can_with_project('update', 2)")
                    td
                        vue-numeric.form-control(v-model="payment.price", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_with_project('update', 2)")
                    td
                        select.form-control(v-model="payment.currency", :disabled="project.finished || !$root.can_with_project('update', 2)")
                            option(v-for="currency in $root.currencies", :value="currency.code") {{ currency.code }}
                    td
                        vue-numeric.form-control(v-model="payment.in_main_currency" separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_with_project('update', 2)")
                    td
                        file-uploader(
                            :file_url="payment.invoice_file"
                            :file_name="payment.invoice_file_name"
                            :editable="!project.finished && $root.can_with_project('update', 2)"
                            @remove="remove_invoice_file(payment)"
                            @finished="(arr) => { [payment.invoice_file, payment.invoice_file_name] = arr }")
                    td
                        file-uploader(
                            :file_url="payment.accounting_statement_file"
                            :file_name="payment.accounting_statement_file_name"
                            :editable="!project.finished && $root.can_with_project('update', 2)"
                            @remove="remove_accounting_statement_file(payment)"
                            @finished="(arr) => { [payment.accounting_statement_file, payment.accounting_statement_file_name] = arr }")
                    td
                        date-picker(:disabled="project.finished || !$root.can_with_project('update', 2)" format="DD.MM.YYYY" v-model="payment.payment_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", :lang="$root.locale", :width="'100%'")
                    td
                        nobr
                            span(v-on:click="toogle_confirmed(payment)" style="width: 80px;")
                                bootstrap-toggle(v-model="payment.confirmed", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 2)")
                            date-picker.mx-2(:disabled="!payment.confirmed || project.finished || !$root.can_with_project('update', 2)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="payment.confirmed_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                    td
                        file-uploader(
                            :file_url="payment.document_file"
                            :file_name="payment.document_file_name"
                            :editable="!project.finished && $root.can_with_project('update', 2)"
                            @remove="remove_document_file(payment)"
                            @finished="(arr) => { [payment.document_file, payment.document_file_name] = arr }")
                    td
                        button.btn(v-on:click="remove_step(payment)", :disabled="project.finished || !$root.can_with_project('update', 2)") {{ $t('template.Remove') }}
            tfoot
                tr
                    td {{ $t('project.Total_paid') }}
                    td
                    td
                    td {{ $root.formatFinanceValue(total_price_in_main_currency) }}
                    td(colspan="5")
        button.btn.btn-diga(v-on:click="add_step", :disabled="project.finished || !$root.can_with_project('update', 2)") {{ $t('template.Add') }}
</template>

<script>
import moment from 'moment';

export default {
    props: {
        project: Object,
        order: Object,
    },
    data: function() {
        return {
            //
        }
    },
    created(){
        if (this.order.transportation_payments.length == 0){
            this.add_step();
        }
    },
    computed: {
        // total_price(){
        //     let total_price = this.order.transportation_payments.map(p => p.confirmed != false ? parseFloat(p.price) : 0).reduce((a, b) => a + b, 0).toFixed(2);
        //     return total_price;
        // },
        total_price_in_main_currency(){
            let total_price = this.order.transportation_payments.map(p => parseFloat(p.in_main_currency)).reduce((a, b) => a + b, 0).toFixed(2);
            this.order.transportation_total = total_price;
            return total_price;
        },
    },
    methods: {
        toogle_confirmed(payment){
            if (!payment.confirmed && payment.confirmed_date == null && !this.project.finished){
                payment.confirmed_date = moment();
            }
        },
        remove_document_file(payment){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                payment.document_file = null;
                payment.document_file_name = null;
            }
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
            let transPayment = {
                id: 0,
                name: '',
                price: 0,
                currency: this.$root.global_settings.currency,
                invoice_file: null,
                invoice_file_name: null,
                accounting_statement_file: null,
                accounting_statement_file_name: null,
                document_file: null,
                document_file_name: null,
                payment_date: '',
                confirmed: false,
                confirmed_date: null,
                in_main_currency: 0,
            };
            this.order.transportation_payments.push(transPayment);
        },
        remove_step(payment){
            if (confirm(this.$root.$t("project.Sure_remove_step"))){
                if (!('removed_transportation_payments' in this.order)){
                    this.order.removed_transportation_payments = [];
                }
                this.order.removed_transportation_payments.push(payment.id);
                let index = this.order.transportation_payments.indexOf(payment);
                this.order.transportation_payments.splice(index, 1);
            }
        },
    },
}
</script>
