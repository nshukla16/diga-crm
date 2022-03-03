<style>
    .contract_payment_steps td{
        vertical-align: middle;
    }
</style>

<template lang="pug">
    div.mb-2
        div.project-section {{ $t('project.Payments') }}
        div.row.mb-2
            div.col-3
                h6 {{ $t('project.Price_of_the_contract') }}
                h5 {{$root.formatFinanceValue(project.contract_price) + ' ' + project.contract_currency}}
                h6 {{ $t('project.Remainder') }}
                h5 {{$root.formatFinanceValue(project.contract_price - total_price) + ' ' + project.contract_currency}}
            div.col-9
                h6 {{ $t('project.Comment') }}
                textarea.form-control(v-model="project.comment_contract_payments", :disabled="project.finished || !$root.can_with_project('update', 3)")
        div.row
            div.col-12
                table.contract_payment_steps.table.table-striped
                    thead
                        tr
                            th(style="width: 140px;") #
                            th(style="width: 50px;") %
                            th(style="width: 160px;") {{ $t('project.In') + ' ' + project.contract_currency }}
                            th(style="width: 160px;" v-if="project.contract_currency != $root.global_settings.currency") {{ $t('project.In') + ' ' + $root.global_settings.currency }}
                            th {{ $t('project.Invoice_template') }}
                            th {{ $t('project.Invoice_sent') }}
                            th {{ $t('project.Invoice') }}
                            th {{ $t('project.Accounting_statement') }}
                            th(style="width: 140px;") {{ $t('project.Payment_date') }}
                            th {{ $t('template.Remove') }}
                    tbody
                        tr(v-for="(payment,i) in project.contract_payments")
                            td {{ i == 0 ? $t('project.Prepayment') : $t('project.Payment') }}
                            td {{ project.contract_price != 0 ? round10(payment.price*100/project.contract_price) : '0' }}
                            td
                                //select.form-control(v-model="payment.currency")
                                    option(v-for="currency in $root.currencies", :value="currency.code") {{ currency.code }}
                                vue-numeric.form-control(v-model="payment.price", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_with_project('update', 3)")
                            td(v-if="project.contract_currency != $root.global_settings.currency")
                                //- input.form-control(v-model="payment.in_main_currency" type="number", :disabled="project.finished || !$root.can_with_project('update', 3)")
                                vue-numeric.form-control(v-model="payment.in_main_currency", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_with_project('update', 3)")
                            td
                                a.clickable.clickable-link(href="#" v-on:click="download_invoice_template(payment.price)") {{ $t('project.Download') }}
                            td
                                nobr
                                    div.d-inline-block(v-on:click="toogle_invoice_sent(payment)")
                                        bootstrap-toggle(v-model="payment.invoice_sent", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 3)")
                                    date-picker.ml-2(:disabled="!payment.invoice_sent || project.finished || !$root.can_with_project('update', 3)" format="DD.MM.YYYY" v-model="payment.invoice_sent_at", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", :lang="$root.locale", :width="'120px'")
                            td
                                file-uploader(
                                    :file_url="payment.invoice_file"
                                    :file_name="payment.invoice_file_name"
                                    :editable="!project.finished && $root.can_with_project('update', 3)"
                                    @remove="remove_invoice_file(payment)"
                                    @finished="(arr) => { [payment.invoice_file, payment.invoice_file_name] = arr }")
                            td
                                file-uploader(
                                    :file_url="payment.accounting_statement_file"
                                    :file_name="payment.accounting_statement_file_name"
                                    :editable="!project.finished && $root.can_with_project('update', 3)"
                                    @remove="remove_accounting_statement_file(payment)"
                                    @finished="(arr) => { [payment.accounting_statement_file, payment.accounting_statement_file_name] = arr }")
                            td
                                date-picker(:disabled="!payment.accounting_statement_file || project.finished || !$root.can_with_project('update', 3)" format="DD.MM.YYYY", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType" v-model="payment.payment_date", :lang="$root.locale", :width="'100%'")
                            td.column_autosize
                                button.btn(v-if="i != 0" v-on:click="remove_step(payment)", :disabled="project.finished || !$root.can_with_project('update', 3)") {{ $t('template.Remove') }}
                    tfoot
                        tr
                            td(style="white-space: nowrap;")
                                span {{ $t('project.Total_paid') }}
                                popper.ml-1(:append-to-body="true")
                                    i.fa.fa-question-circle-o(slot="reference")
                                    div.popper {{ $t('project.Summarized_fields_with_payment_date') }}

                            td
                            td {{ $root.formatFinanceValue(total_price) }}
                            td(v-if="project.contract_currency != $root.global_settings.currency") {{ $root.formatFinanceValue(total_price_in_main_currency) }}
                            td(:colspan="project.contract_currency != $root.global_settings.currency ? 5 : 4")
                button.btn.btn-diga(v-on:click="add_step", :disabled="project.finished || !$root.can_with_project('update', 3)") {{ $t('template.Add') }}
</template>

<script>
import moment from 'moment';

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
        if (this.project.contract_payments.length == 0){ // if it is creating of new project, add prepayment field
            this.add_step();
        }
    },
    computed: {
        total_price(){
            let total_price = this.project.contract_payments.map(p => p.payment_date != null ? parseFloat(p.price) : 0).reduce((a, b) => a + b, 0).toFixed(2);
            if (this.project.contract_currency == this.$root.global_settings.currency){
                this.project.contract_total_price = total_price;
            }
            return total_price;
        },
        total_price_in_main_currency(){
            let total_price = this.project.contract_payments.map(p => p.payment_date != null ? parseFloat(p.in_main_currency) : 0).reduce((a, b) => a + b, 0).toFixed(2);
            if (this.project.contract_currency != this.$root.global_settings.currency) {
                this.project.contract_total_price = total_price;
            }
            return total_price;
        },
    },
    methods: {
        toogle_invoice_sent(payment){
            if (!payment.invoice_sent && payment.invoice_sent_at == null && !this.project.finished){
                payment.invoice_sent_at = moment();
            }
        },
        download_invoice_template(val){
            this.$root.global_loading = true;
            this.$http.get('/api/payment_invoice/export?project_id=' + this.project.id + '&val=' + val, {responseType: 'blob'}).then(response => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'invoice-' + moment().format("DD-MM-YYYY-HH-mm-ss") + '.docx');
                document.body.appendChild(link);
                link.click();
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
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
                invoice_sent: false,
                invoice_sent_at: null,
                invoice_file: null,
                invoice_file_name: null,
                accounting_statement_file: null,
                accounting_statement_file_name: null,
                payment_date: null,
                in_main_currency: 0,
            };
            this.project.contract_payments.push(contractPayment);
        },
        remove_step(payment){
            if (confirm(this.$root.$t("project.Sure_remove_step"))){
                if (!('removed_contract_steps' in this.project)){
                    this.project.removed_contract_steps = [];
                }
                this.project.removed_contract_steps.push(payment);
                let index = this.project.contract_payments.indexOf(payment);
                this.project.contract_payments.splice(index, 1);
            }
        },
        round10: function (num){
            return this.$root.roundNumber(num, 2);
        },
    },
}
</script>
