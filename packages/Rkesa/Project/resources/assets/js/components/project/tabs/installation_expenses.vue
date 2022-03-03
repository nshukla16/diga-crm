<style>
    .contract_payment_steps td{
        vertical-align: middle;
    }
</style>

<template lang="pug">
    div.mb-2
        div.project-section {{ $t('project.Expenses') }}
        div.row
            div.col-6.col-lg-12
                table.contract_payment_steps.table.table-striped
                    thead
                        tr
                            th(style="width: 100px;") #
                            th(style="width: 140px;") {{ $t('project.Name_of_payment') }}
                            th(style="width: 100px;") {{ $t('project.Value') }}
                            th(style="width: 100px;") {{ $t('project.Currency') }}
                            th(style="width: 130px;") {{ $t('project.In') + ' ' + $root.global_settings.currency }}
                            th(style="width: 150px;") {{ $t('project.Date') }}
                            th {{ $t('project.Invoice_sent') }}
                            th {{ $t('project.Invoice') }}
                            th {{ $t('project.Accounting_statement') }}
                            th {{ $t('project.Confirmed') }}
                            th {{ $t('project.Confirmed_date') }}
                            th {{ $t('template.Remove') }}
                    tbody
                        tr(v-for="(payment,i) in project.installation_expense_payments")
                            td {{ $t('project.Payment') }}
                            td
                                input.form-control(v-model="payment.payment_name" type="text", :disabled="project.finished || !$root.can_with_project('update', 4)")
                            td
                                vue-numeric.form-control(v-bind:minus="true" v-model="payment.payment_value", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_with_project('update', 4)")
                            td
                                select.form-control(v-model="payment.payment_currency", :disabled="project.finished || !$root.can_with_project('update', 4)")
                                    option(v-for="currency in $root.currencies", :value="currency.code") {{ currency.code }}
                            td
                                vue-numeric.form-control(v-bind:minus="true" v-model="payment.payment_main_currency", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_with_project('update', 4)")
                            td
                                date-picker.ml-2(format="DD.MM.YYYY" v-model="payment.payment_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", :lang="$root.locale", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 4)")
                            td(style="text-align: center")
                                //input(type="checkbox" v-model="payment.payment_invoice_sent", :disabled="project.finished || !$root.can_with_project('update', 4)")
                                bootstrap-toggle(v-model="payment.payment_invoice_sent", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 4)")
                            td
                                file-uploader(
                                    :file_url="payment.payment_invoice_file_path"
                                    :file_name="payment.payment_invoice_file_name"
                                    :editable="!project.finished && $root.can_with_project('update', 4)"
                                    @remove="remove_invoice_file(payment)"
                                    @finished="(arr) => { [payment.payment_invoice_file_path, payment.payment_invoice_file_name] = arr }")
                            td
                                file-uploader(
                                    :file_url="payment.payment_accounting_file_path"
                                    :file_name="payment.payment_accounting_file_name"
                                    :editable="!project.finished && $root.can_with_project('update', 4)"
                                    @remove="remove_accounting_statement_file(payment)"
                                    @finished="(arr) => { [payment.payment_accounting_file_path, payment.payment_accounting_file_name] = arr }")
                            td
                                bootstrap-toggle(v-model="payment.payment_confirmed", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 4)")
                            td
                                date-picker(format="DD.MM.YYYY" v-model="payment.payment_confirmed_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", :lang="$root.locale", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 4)")
                            td
                                button.btn(v-if="i != 0" v-on:click="remove_step(payment)", :disabled="project.finished || !$root.can_with_project('update', 4)") {{ $t('template.Remove') }}
                    tfoot
                        tr
                            td(style="white-space: nowrap;")
                                span {{ $t('project.Total_paid') }}
                                popper.ml-1(:append-to-body="true")
                                    i.fa.fa-question-circle-o(slot="reference")
                                    div.popper {{ $t('project.Summarized_fields_with_payment_date') }}
                            td
                            td
                            td
                            td {{ $root.formatFinanceValue(total_price_in_main_currency) }}
                            td(colspan="5")
                button.btn.btn-diga(v-on:click="add_step", :disabled="project.finished || !$root.can_with_project('update', 4)") {{ $t('template.Add') }}
</template>

<script>
export default {
    props: {
        project: {
            type: Object,
        },
    },
    data: function() {
        return {
            //
        }
    },
    created(){
        if (this.project.installation_expense_payments.length == 0){ // if it is creating of new project, add prepayment field
            this.add_step();
        }
    },
    computed: {
        total_price(){
            let total_price = this.project.installation_expense_payments.map(p => p.payment_date != null ? parseFloat(p.payment_value) : 0).reduce((a, b) => a + b, 0).toFixed(2);
            return total_price;
        },
        total_price_in_main_currency(){
            let total_price = this.project.installation_expense_payments.map(p => p.payment_date != null ? parseFloat(p.payment_main_currency) : 0).reduce((a, b) => a + b, 0).toFixed(2);
            this.project.installation_total_price = total_price;
            return total_price;
        },
    },
    methods: {
        remove_accounting_statement_file(payment){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                payment.payment_accounting_file_path = null;
                payment.payment_accounting_file_name = null;
            }
        },
        remove_invoice_file(payment){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                payment.payment_invoice_file_path = null;
                payment.payment_invoice_file_name = null;
            }
        },
        add_step(){
            let contractPayment = {
                id: 0,
                payment_name: '',
                payment_value: 0,
                payment_main_currency: 0,
                currency: this.$root.global_settings.currency,
                payment_invoice_sent: false,
                payment_date: null,
                payment_invoice_file_name: null,
                payment_invoice_file_path: null,
                payment_accounting_file_name: null,
                payment_accounting_file_path: null,
                payment_confirmed: false,
                payment_confirmed_date: '',
            };
            this.project.installation_expense_payments.push(contractPayment);
        },
        remove_step(payment){
            if (confirm(this.$root.$t("project.Sure_remove_step"))){
                if (!('removed_expense_payments' in this.project)){
                    this.project.removed_expense_payments = [];
                }
                this.project.removed_expense_payments.push(payment.id);
                let index = this.project.installation_expense_payments.indexOf(payment);
                this.project.installation_expense_payments.splice(index, 1);
            }
        },
    },
}
</script>
