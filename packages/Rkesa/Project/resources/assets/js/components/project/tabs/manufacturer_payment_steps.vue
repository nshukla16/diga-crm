<style>
    .contract_payment_steps td{
        vertical-align: middle;
    }
</style>

<template lang="pug">
    div.mb-2
        div.project-section {{ $t('project.Manufacturer_payments') }}
        div.row
            div.col-12.col-lg-6
                fieldset.form-group
                    label.w-100 {{ $t('project.Seller')+': ' + manufacturer.manufacturer.name }}
                div.form-group.row
                    label.col-6.input-line {{ $t('project.Buyer') }}
                    div.col-6
                        select.form-control(v-model="manufacturer.buyer_legal_entity_id", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            option(v-for="entity in legal_entities", :value="entity.id") {{ entity.name }}
                div.form-group.row
                    label.col-6.input-line {{ $t('project.Total_price_to_pay') }}
                    div.col-6
                        div.row
                            div.col-8
                                vue-numeric.form-control(v-model="manufacturer.need_to_pay", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            div.col-4
                                select.form-control(v-model="manufacturer.contract_currency", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                    option(v-for="currency in $root.currencies", :value="currency.code") {{ currency.name + ' (' + currency.code + ')' }}
                fieldset.form-group
                    label.w-100 {{ $t('project.Remainder') }}
                    h5 {{$root.formatFinanceValue(manufacturer.need_to_pay - total_price) + ' ' + manufacturer.contract_currency}}            
            div.col-12.col-lg-6
                h6 {{ $t('project.Comment') }}
                textarea.form-control(v-model="manufacturer.comment_manufacturer_payments", :disabled="project.finished || !$root.can_with_project('update', 1)")
        div.row
            div.col-12
                table.contract_payment_steps.table.table-striped
                    thead
                        tr
                            th(style="width: 140px;") #
                            th(style="width: 50px;") %
                            th(style="width: 160px;") {{ $t('project.In') + ' ' + manufacturer.contract_currency }}
                            th(style="width: 160px;" v-if="manufacturer.contract_currency != $root.global_settings.currency") {{ $t('project.In') + ' ' + $root.global_settings.currency }}
                            th {{ $t('project.Invoice') }}
                            th {{ $t('project.Accounting_statement') }}
                            th {{ $t('project.Payment_date') }}
                            th(style="width: 260px;" v-if="$root.can_with_project('read', 6)") {{ $t('project.Confirmed') }}
                            th {{ $t('template.Remove') }}
                    tbody
                        tr(v-for="(payment,i) in manufacturer.payments")
                            td {{ i == 0 ? $t('project.Prepayment') : $t('project.Payment') }}
                            td {{ manufacturer.need_to_pay != 0 ? round10(payment.price*100/manufacturer.need_to_pay) : '0' }}
                                //input.form-control(v-model="payment.percent" type="number")
                            td
                                //select.form-control(v-model="payment.currency")
                                    option(v-for="currency in $root.currencies", :value="currency.code") {{ currency.code }}
                                vue-numeric.form-control(v-model="payment.price", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            td(v-if="manufacturer.contract_currency != $root.global_settings.currency")
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
                            td(v-if="$root.can_with_project('read', 6)")
                                bootstrap-toggle(v-model="payment.confirmed", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 1) || !$root.can_with_project('update', 6)")
                                date-picker.mx-2(:disabled="!payment.confirmed || project.finished || !$root.can_with_project('update', 1) || !$root.can_with_project('update', 6)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="payment.confirmed_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
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
                            td {{ $root.formatFinanceValue(total_price) }}
                            td(v-if="manufacturer.contract_currency != $root.global_settings.currency") {{ $root.formatFinanceValue(total_price_in_main_currency) }}
                            td(:colspan="manufacturer.contract_currency != $root.global_settings.currency ? 4 : 5")
                button.btn.btn-diga(v-on:click="add_step", :disabled="project.finished || !$root.can_with_project('update', 1)") {{ $t('template.Add') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: {
        project: {
            type: Object,
        },
        manufacturer: Object,
    },
    data: function() {
        return {
            //
        }
    },
    created(){
        if (this.manufacturer.payments.length == 0){ // if it is creating of new project, add prepayment field
            this.add_step();
        }
    },
    computed: {
        ...mapGetters({
            legal_entities: 'getLegalEntities',
        }),
        total_price(){
            let total_price = this.manufacturer.payments.map(p => p.payment_date != null ? parseFloat(p.price) : 0).reduce((a, b) => a + b, 0).toFixed(2);
            if (this.manufacturer.contract_currency == this.$root.global_settings.currency) {
                this.manufacturer.payments_total_price = total_price;
            }
            return total_price;
        },
        total_price_in_main_currency(){
            let total_price = this.manufacturer.payments.map(p => p.payment_date != null ? parseFloat(p.in_main_currency) : 0).reduce((a, b) => a + b, 0).toFixed(2);
            if (this.manufacturer.contract_currency != this.$root.global_settings.currency) {
                this.manufacturer.payments_total_price = total_price;
            }
            return total_price;
        },
    },
    methods: {
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
            this.manufacturer.payments.push(contractPayment);
        },
        remove_step(payment){
            if (confirm(this.$root.$t("project.Sure_remove_step"))){
                if (!('removed_manufacturer_steps' in this.manufacturer)){
                    this.manufacturer.removed_manufacturer_steps = [];
                }
                this.manufacturer.removed_manufacturer_steps.push(payment.id);
                let index = this.manufacturer.payments.indexOf(payment);
                this.manufacturer.payments.splice(index, 1);
            }
        },
        round10: function (num){
            return this.$root.roundNumber(num, 2);
        },
    },
}
</script>
