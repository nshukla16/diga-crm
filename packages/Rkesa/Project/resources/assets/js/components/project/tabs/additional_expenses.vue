<style>
</style>

<template lang="pug">
    div.mb-2
        div.project-section {{ $t('project.additional_expenses_block_full') }}
        table.table.table-striped
            thead
                tr
                    th(style="width: 300px;") {{ $t('project.Purpose_of_payment') }}
                    th(style="width: 100px;") {{ $t('project.Value') }}
                    th(style="width: 100px;") {{ $t('project.Currency') }}
                    th(style="width: 130px;") {{ $t('project.In') + ' ' + $root.global_settings.currency }}
                    th {{ $t('project.Date') }}
                    th {{ $t('client.comment') }}
                    th {{ $t('project.Document') }}
                    th {{ $t('template.Remove') }}
            tbody
                tr(v-for="(payment,i) in project.additional_expenses")
                    td
                        input.form-control(v-model="payment.name", :disabled="project.finished || !$root.can_with_project('update', 9)")
                    td
                        vue-numeric.form-control(:read-only="project.finished || !$root.can_with_project('update', 9)" v-bind:minus="true" v-model="payment.price", separator=",", v-bind:precision="2", :disabled="!$root.can_with_project('update', 9)")
                    td
                        select.form-control(v-model="payment.currency", :disabled="project.finished || !$root.can_with_project('update', 9)")
                            option(v-for="currency in $root.currencies", :value="currency.code") {{ currency.code }}
                    td
                        vue-numeric.form-control(:read-only="project.finished || !$root.can_with_project('update', 9)" v-bind:minus="true" v-model="payment.in_main_currency", separator=",", v-bind:precision="2", :disabled="!$root.can_with_project('update', 9)")
                    td
                        date-picker(:disabled="project.finished || !$root.can_with_project('update', 9)" format="DD.MM.YYYY" v-model="payment.payment_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", :lang="$root.locale", :width="'100%'")
                    td
                        textarea.form-control(:disabled="project.finished || !$root.can_with_project('update', 9)" v-model="payment.comment")
                    td
                        file-uploader(
                            :file_url="payment.document_file"
                            :file_name="payment.document_file_name"
                            :editable="!project.finished && $root.can_with_project('update', 9)"
                            @remove="remove_document_file(payment)"
                            @finished="(arr) => { [payment.document_file, payment.document_file_name] = arr }")
                    td
                        button.btn(v-on:click="remove_step(payment)", :disabled="project.finished || !$root.can_with_project('update', 9)") {{ $t('template.Remove') }}
            tfoot
                tr
                    td {{ $t('project.Total_paid') }}
                    td
                    td
                    td {{ $root.formatFinanceValue(total_price_in_main_currency) }}
                    td(colspan="5")
        button.btn.btn-diga(v-on:click="add_step", :disabled="project.finished || !$root.can_with_project('update', 9)") {{ $t('template.Add') }}
</template>

<script>
import moment from 'moment';

export default {
    props: {
        project: Object,
    },
    data: function() {
        return {
        }
    },
    created(){
        if (this.project.additional_expenses.length == 0){
            this.add_step();
        }
    },
    computed: {
        total_price_in_main_currency(){
            let total_price = this.project.additional_expenses.map(p => p.in_main_currency).reduce((a, b) => a + b, 0);            
            return total_price;
        },
    },
    methods: {
        remove_document_file(payment){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                payment.document_file = null;
                payment.document_file_name = null;
            }
        },
        add_step(){
            let payment = {
                id: 0,
                project_id: this.project.id,
                name: '',
                price: 0,
                currency: this.$root.global_settings.currency,
                document_file: null,
                document_file_name: null,
                payment_date: '',
                in_main_currency: 0,
            };
            this.project.additional_expenses.push(payment);
        },
        remove_step(payment){
            if (confirm(this.$root.$t("project.Sure_remove_step"))){
                if (!('removed_additional_expenses' in this.project)){
                    this.project.removed_additional_expenses = [];
                }
                this.project.removed_additional_expenses.push(payment.id);
                let index = this.project.additional_expenses.indexOf(payment);
                this.project.additional_expenses.splice(index, 1);
            }
        },
    },
}
</script>
