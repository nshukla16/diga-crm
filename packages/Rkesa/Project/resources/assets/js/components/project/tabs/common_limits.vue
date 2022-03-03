<style>
    @media (max-width: 760px) {
        .tabs_container > div {
            font-size: 12px;
            padding: 10px 10px;
        }
    }
</style>

<template lang="pug">
    div.mb-2
        div.project-section {{ $t('project.Limits') }}
        div.row
            div.col-9
                div.row
                    div.col-4.mb-2
                        h6 {{ $t('project.Type_of_limits') }}
                        select.form-control(style="display: inline-block;min-width: 150px;flex:1;" v-model="project.limit_type", :disabled="project.finished || !$root.can_with_project('update', 3)")
                            option(:value="0") {{ $t('project.Limit_for_supplying_transporting') }}
                            option(:value="1") {{ $t('project.Limit_for_shipment') }}
                    div.col-4
                        h6 {{ $t('project.Date_forming_type') }}
                        select.form-control(style="display: inline-block;min-width: 150px;flex:1;" v-model="project.limit_forming_type", :disabled="project.finished || !$root.can_with_project('update', 3)")
                            option(:value="0") {{ $t('project.Amount_days') }}
                            option(:value="1") {{ $t('project.Before_date') }}
                    div.col-4(v-if="project.limit_forming_type == 0")
                        h6 {{ $t('project.Date') }}
                        select.form-control(style="display: inline-block;min-width: 150px;flex:1; margin: 0 0 1.5rem 0" v-model="project.limit_forming_date", :disabled="project.finished || !$root.can_with_project('update', 3)")
                            option(:value="0") {{ $t('project.Date_of_prepayment') }}
                            option(:value="1") {{ $t('project.Date_of_signing_contract') }}
                        h6 {{ $t('project.Days') }}
                        input.form-control(v-model="project.limit_forming_days" type="number" min="1" style="margin: 0 0 1.5rem 0", :disabled="project.finished || !$root.can_with_project('update', 3)")
                        div(v-if="project.limit_forming_date == 0")
                            div(v-if="project.contract_payments[0] && project.contract_payments[0].payment_date")
                                div {{ $t('project.Date_of_prepayment') }} : {{ prepayment_date }}
                                div {{ project.limit_type == 0 ? $t('project.Delivery_date') : $t('project.Shipping_date') }}: {{ limit_prepayment_date_final }}
                            div(v-else) {{ $t('project.Date_of_prepayment') }}: {{ $t('project.Not_set') }}
                        div(v-if="project.limit_forming_date == 1")
                            div(v-if="project.date_of_sign_contract")
                                div {{ $t('project.Date_of_signing_contract') }} : {{ contract_signing_date }}
                                div {{ project.limit_type == 0 ? $t('project.Delivery_date') : $t('project.Shipping_date') }}: {{ limit_contract_signing_date_final }}
                            div(v-else) {{ $t('project.Date_of_signing_contract') }}: {{ $t('project.Not_set') }}
                    div.col-4(v-if="project.limit_forming_type == 1")
                        h6 {{ $t('project.Date') }}
                        date-picker(:lang="$root.locale" v-model="project.limit_before_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", format="DD.MM.YYYY", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 3)")
            div.col-3
                h6 {{ $t('project.Comment') }}
                textarea.form-control(rows="5" cols="50" v-model="project.comment_limits", :disabled="project.finished || !$root.can_with_project('update', 3)")
        // only if direct contract type
        div.row.mb-2(v-if="project.contract_type == 1")
            div.col-9
                div.row.mb-2(v-for="fact_delivery_entity in project.fact_delivery_entities")
                    label.col-3.input-line {{ $t('project.Fact_delivery_date') }}
                    div.col-5.d-flex
                        div.d-inline-block(v-on:click="toogle_fact_delivery(fact_delivery_entity)")
                            bootstrap-toggle(v-model="fact_delivery_entity.exist", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 3)")
                        date-picker.mx-2(:disabled="project.finished || !$root.can_with_project('update', 3)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="fact_delivery_entity.date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140" style="min-width:140px;")
                        textarea.form-control.mr-2(v-model="fact_delivery_entity.notes")
                        button.btn.btn-danger(style="height: 38px;" @click="remove_fact_delivery(fact_delivery_entity)", :disabled="project.finished || !$root.can_with_project('update', 3)")
                            i.fa.fa-times
                button.btn.btn-diga(@click="add_fact_delivery", :disabled="project.finished || !$root.can_with_project('update', 3)") {{ $t('project.Add_fact_delivery_date') }}
        div.row
            div.col-9
                div.row.mb-2(v-for="contract in project.additional_contracts")
                    label.col-2.input-line {{ $t('project.Additional_contract_number') }}
                    div.col-4.d-flex
                        input.form-control.mr-2(style="flex: 1;" type="text", v-model="contract.contract_number", :disabled="project.finished || !$root.can_with_project('update', 3)")
                        div.input-line(style="flex: 1;")
                            file-uploader(
                                :file_url="contract.contract_file"
                                :file_name="contract.contract_file_name"
                                :editable="!project.finished && $root.can_with_project('update', 3)"
                                @remove="remove_addit_contract_file(contract)"
                                @finished="(arr) => { [contract.contract_file, contract.contract_file_name] = arr }")
                        button.btn.btn-danger(@click="remove_addit_contract(contract)", :disabled="project.finished || !$root.can_with_project('update', 3)")
                            i.fa.fa-times
                button.btn.btn-diga(@click="add_addit_contract", :disabled="project.finished || !$root.can_with_project('update', 3)") {{ $t('project.Add_additional_contract') }}

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
    methods: {
        add_addit_contract(){
            let newAddit = {
                id: 0,
                contract_number: '',
                contract_file: null,
                contract_file_name: null,

            };
            this.project.additional_contracts.push(newAddit);
        },
        remove_addit_contract(contract){
            if (confirm(this.$root.$t("project.Sure_remove_addit_contract"))){
                if (!('removed_addit_contracts' in this.project)){
                    this.project.removed_addit_contracts = [];
                }
                this.project.removed_addit_contracts.push(contract.id);
                let index = this.project.additional_contracts.indexOf(contract);
                this.project.additional_contracts.splice(index, 1);
            }
        },
        remove_addit_contract_file(contract){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                contract.contract_file = null;
                contract.contract_file_name = null;
            }
        },
        toogle_fact_delivery(fact_delivery_entity){
            if (!fact_delivery_entity.exist && fact_delivery_entity.date == null && !this.project.finished){
                fact_delivery_entity.date = moment();
            }
        },
        remove_fact_delivery(fact_delivery_entity){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_fact_delivery"))) {
                if (!('removed_fact_deliveries' in this.project)){
                    this.project.removed_fact_deliveries = [];
                }
                this.project.removed_fact_deliveries.push(fact_delivery_entity.id);
                let index = this.project.fact_delivery_entities.indexOf(fact_delivery_entity);
                this.project.fact_delivery_entities.splice(index, 1);
            }
        },
        add_fact_delivery(){
            let f = {
                id: 0,
                exist: false,
                date: null,
                notes: '',
            };
            this.project.fact_delivery_entities.push(f);
        },
    },
    computed: {
        prepayment_date(){
            return moment(this.project.contract_payments[0].payment_date).format('DD.MM.YYYY');
        },
        limit_prepayment_date_final(){
            return moment(this.project.contract_payments[0].payment_date).add(this.project.limit_forming_days, 'days').format('DD.MM.YYYY');
        },
        contract_signing_date(){
            return moment(this.project.date_of_sign_contract).format('DD.MM.YYYY');
        },
        limit_contract_signing_date_final(){
            return moment(this.project.date_of_sign_contract).add(this.project.limit_forming_days, 'days').format('DD.MM.YYYY');
        },
    },
}
</script>
