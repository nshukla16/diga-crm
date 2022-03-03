<style>
    .contract_payment_steps td{
        vertical-align: middle;
    }
</style>

<template lang="pug">
    div.mb-2
        div.project-section {{ $t('project.Inner_contracts_and_project_payments') }}
        div.row
            div.col-12.col-lg-5
                div.form-group.row
                    label.col-6.input-line {{ $t('project.Seller') }}
                    div.col-6
                        select.form-control(v-model="manufacturer.inner_seller_legal_entity_id", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            option(v-for="entity in legal_entities", :value="entity.id") {{ entity.name }}
                div.form-group.row
                    label.col-6.input-line {{ $t('project.Buyer') }}
                    div.col-6
                        select.form-control(v-model="manufacturer.inner_buyer_legal_entity_id", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            option(v-for="entity in legal_entities", :value="entity.id") {{ entity.name }}
                div.form-group.row
                    label.col-6.input-line {{ $t('project.Inner_price_to_pay') }}
                    div.col-6
                        div.row
                            div.col-8
                                vue-numeric.form-control(v-model="manufacturer.inner_need_to_pay", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            div.col-4
                                select.form-control(v-model="manufacturer.inner_contract_currency", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                    option(v-for="currency in $root.currencies", :value="currency.code") {{ currency.name + ' (' + currency.code + ')' }}
                fieldset.form-group
                    label.w-100 {{ $t('project.Remainder') }}
                    h5 {{$root.formatFinanceValue(manufacturer.inner_need_to_pay - total_price) + ' ' + manufacturer.inner_contract_currency}}
            div.col-12.col-lg-7
                div.form-group.row
                    label.col-6.input-line {{ $t('project.Inner_contract_number') }}
                    div.col-6.d-flex.input-line
                        template(v-if="inner_seller_legal_entity && inner_seller_legal_entity.contracts.length > 0")
                            select.form-control.mr-2(style="flex:1;", v-model="manufacturer.inner_contract_legal_entity_contract_id", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                option(:value="0") {{ $t('project.Not_selected') }}
                                option(v-for="contract in inner_seller_legal_entity.contracts", :value="contract.id") {{ contract.name }}
                            div.mr-2(v-if="manufacturer.inner_contract_legal_entity_contract_id" style="flex:1;")
                                a.input-line.short-link(:href="current_contract.file" target="_blank", :title="current_contract.file_name") {{ current_contract.file_name }}
                        div(v-else style="margin-left: auto;") {{ $t('project.No_contracts') }}
                        //template(v-if="manufacturer.inner_contract_from_db")
                        //template(v-else)
                            input.form-control.mr-2(v-model="manufacturer.inner_contract_number", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            div.mr-2(style="flex: 1;")
                                file-uploader(
                                    :file_url="manufacturer.inner_contract_file"
                                    :file_name="manufacturer.inner_contract_file"
                                    :editable="!project.finished && $root.can_with_project('update', 1)"
                                    @remove="remove_inner_contract_file"
                                    @finished="(arr) => { [manufacturer.inner_contract_file, manufacturer.inner_contract_file] = arr }")
                        //div(style="width:100px;" @click="select_first_inner_contract()")
                            bootstrap-toggle(v-model="manufacturer.inner_contract_from_db", :options="{ on: $t('project.From_db'), off: $t('project.File')}", data-width="100", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 1)")
                div.form-group
                    div(v-for="(spec,i) in manufacturer.inner_specifications")
                        div.form-group.row
                            label.col-4.input-line {{ $t('project.Specification_number') }}
                            div.col-8.d-flex
                                template(v-if="inner_seller_legal_entity && inner_seller_legal_entity.contracts.length > 0")
                                    select.form-control.mr-2(style="flex:1;", v-model="spec.legal_entity_contract_id", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                        option(v-for="contract in inner_seller_legal_entity.contracts", :value="contract.id") {{ contract.name }}
                                    div.mr-2(v-if="spec.legal_entity_contract_id" style="flex:1;")
                                        a.input-line.short-link(:href="inner_seller_contracts_by_id[spec.legal_entity_contract_id].file" target="_blank", :title="inner_seller_contracts_by_id[spec.legal_entity_contract_id].file_name") {{ inner_seller_contracts_by_id[spec.legal_entity_contract_id].file_name }}
                                div(v-else style="margin-left: auto;") {{ $t('project.No_contracts') }}
                                //template(v-if="spec.from_db")
                                //template(v-else)
                                    input.form-control.mr-2(style="flex: 1;" type="text", v-model="spec.number", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                    div.input-line.mr-2(style="flex: 1;")
                                        file-uploader(
                                            :file_url="spec.file"
                                            :file_name="spec.file_name"
                                            :editable="!project.finished && $root.can_with_project('update', 1)"
                                            @remove="remove_specification_file(spec)"
                                            @finished="(arr) => { [spec.file, spec.file_name] = arr }")
                                //div(style="width:100px;" @click="select_first_inner_spec(spec)")
                                    bootstrap-toggle(v-model="spec.from_db", :options="{ on: $t('project.From_db'), off: $t('project.File')}", data-width="100", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                button.btn.btn-danger.ml-2(v-on:click="remove_spec(spec)", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                    i.fa.fa-times
                        div.form-group.row
                            label.col-4.input-line {{ $t("project.Conditions_of_delivery_for_specification") }}
                            div.col-8
                                select.form-control(v-model="spec.delivery_conditions", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                    option(:value="i" v-for="(v,i) in conditions") {{ v }}
                        div.form-group
                            div(v-for="contract in spec.additional_contracts")
                                div.form-group.row
                                    label.col-4.input-line {{ $t('project.Additional_contract_number') }}
                                    div.col-8.d-flex
                                        template(v-if="inner_seller_legal_entity && inner_seller_legal_entity.contracts.length > 0")
                                            select.form-control.mr-2(style="flex:1;", v-model="contract.legal_entity_contract_id", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                                option(v-for="contract in inner_seller_legal_entity.contracts", :value="contract.id") {{ contract.name }}
                                            div.mr-2(v-if="contract.legal_entity_contract_id" style="flex:1;")
                                                a.input-line.short-link(:href="inner_seller_contracts_by_id[contract.legal_entity_contract_id].file" target="_blank", :title="inner_seller_contracts_by_id[contract.legal_entity_contract_id].file_name") {{ inner_seller_contracts_by_id[contract.legal_entity_contract_id].file_name }}
                                        div(v-else style="margin-left: auto;") {{ $t('project.No_contracts') }}
                                        //template(v-if="contract.from_db")
                                        //template(v-else)
                                            input.form-control.mr-2(style="flex: 1;" type="text", v-model="contract.number", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                            div.input-line.mr-2(style="flex: 1;")
                                                file-uploader(
                                                    :file_url="contract.file"
                                                    :file_name="contract.file_name"
                                                    :editable="!project.finished && $root.can_with_project('update', 1)"
                                                    @remove="remove_addit_contract_file(contract)"
                                                    @finished="(arr) => { [contract.file, contract.file_name] = arr }")
                                        //div(style="width:100px;" @click="select_first_inner_spec(contract)")
                                            bootstrap-toggle(v-model="contract.from_db", :options="{ on: $t('project.From_db'), off: $t('project.File')}", data-width="100", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                        button.btn.btn-danger.ml-2(v-on:click="remove_addit_contract(spec, contract)", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                            i.fa.fa-times
                            button.btn.btn-diga(v-on:click="add_addit_contract(spec)", :disabled="project.finished || !$root.can_with_project('update', 1)") {{ $t('project.Add_additional_contract') }}
                    button.btn.btn-diga(v-on:click="add_spec", :disabled="project.finished || !$root.can_with_project('update', 1)") {{ $t('project.Add_specification') }}
        div.row.mb-2
            div.col-12
                h6 {{ $t('project.Comment') }}
                textarea.form-control(v-model="manufacturer.comment_inner_payments", :disabled="project.finished || !$root.can_with_project('update', 1)")
        div.row
            div.col-12
                table.contract_payment_steps.table.table-striped
                    thead
                        tr
                            th(style="width: 140px;") #
                            th(style="width: 100px;") %
                            th(style="width: 160px;") {{ $t('project.In') + ' ' + manufacturer.inner_contract_currency }}
                            th(style="width: 160px;" v-if="manufacturer.inner_contract_currency != $root.global_settings.currency") {{ $t('project.In') + ' ' + $root.global_settings.currency }}
                            th {{ $t('project.Invoice') }}
                            th {{ $t('project.Accounting_statement') }}
                            th {{ $t('project.Payment_date') }}
                            th(style="width: 260px;") {{ $t('project.Confirmed') }}
                            th {{ $t('template.Remove') }}
                    tbody
                        tr(v-for="(payment,i) in manufacturer.inner_payments")
                            td {{ i == 0 ? $t('project.Prepayment') : $t('project.Payment') }}
                            td {{ manufacturer.inner_need_to_pay != 0 ? round10(payment.price*100/manufacturer.inner_need_to_pay) : '0' }}
                            td
                                //select.form-control(v-model="payment.currency")
                                    option(v-for="currency in $root.currencies", :value="currency.code") {{ currency.code }}
                                vue-numeric.form-control(v-model="payment.price", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            td(v-if="manufacturer.inner_contract_currency != $root.global_settings.currency")
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
                            td
                                bootstrap-toggle(v-model="payment.confirmed", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                date-picker.mx-2(:disabled="!payment.confirmed || project.finished || !$root.can_with_project('update', 1)", :lang="$root.locale" v-model="payment.confirmed_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
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
                            td(v-if="manufacturer.inner_contract_currency != $root.global_settings.currency") {{ total_price_in_main_currency }}
                            td(colspan="8")
                button.btn.btn-diga(v-on:click="add_step", :disabled="project.finished || !$root.can_with_project('update', 1)") {{ $t('template.Add') }}
</template>

<script>
import {mapGetters} from "vuex";
import {inner_conditions_of_delivery} from "../../../helper"

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
        if (this.manufacturer.inner_payments.length == 0){ // if it is creating of new project, add prepayment field
            this.add_step();
        }
    },
    watch: {
        'manufacturer.inner_seller_legal_entity_id': function(){
            this.sync_inner_contract_db();
            this.manufacturer.inner_specifications.forEach(spec => {
                this.sync_inner_spec_db(spec);
                spec.additional_contracts.forEach(contract => {
                    this.sync_inner_spec_db(contract);
                })
            });
        },
    },
    computed: {
        ...mapGetters({
            legal_entities: 'getLegalEntities',
            legal_entities_by_id: 'getLegalEntitiesById',
        }),
        total_price(){
            let total_price = this.manufacturer.inner_payments.map(p => p.payment_date != null ? parseFloat(p.price) : 0).reduce((a, b) => a + b, 0).toFixed(2);
            return total_price;
        },
        total_price_in_main_currency(){
            let total_price = this.manufacturer.inner_payments.map(p => p.payment_date != null ? parseFloat(p.in_main_currency) : 0).reduce((a, b) => a + b, 0).toFixed(2);
            return total_price;
        },
        current_contract(){
            return this.inner_seller_contracts_by_id[this.manufacturer.inner_contract_legal_entity_contract_id];
        },
        inner_seller_legal_entity(){
            return this.legal_entities_by_id[this.manufacturer.inner_seller_legal_entity_id];
        },
        inner_seller_contracts_by_id(){
            return this.inner_seller_legal_entity.contracts.reduce(function(map, obj) {
                map[obj.id] = obj;
                return map;
            }, {});
        },
        conditions(){
            return inner_conditions_of_delivery;
        },
    },
    methods: {
        remove_inner_contract_file(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.manufacturer.inner_contract_file = null;
                this.manufacturer.inner_contract_file_name = null;
            }
        },
        select_first_inner_contract(){
            if (!this.manufacturer.inner_contract_from_db && this.manufacturer.inner_contract_legal_entity_contract_id == null){
                this.sync_inner_contract_db();
            }
        },
        sync_inner_contract_db(){
            if (this.inner_seller_legal_entity.contracts.length > 0) {
                this.manufacturer.inner_contract_legal_entity_contract_id = this.inner_seller_legal_entity.contracts[0].id;
            } else {
                this.manufacturer.inner_contract_legal_entity_contract_id = null;
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
            this.manufacturer.inner_payments.push(contractPayment);
        },
        remove_step(payment){
            if (confirm(this.$root.$t("project.Sure_remove_step"))){
                if (!('removed_inner_steps' in this.manufacturer)){
                    this.manufacturer.removed_inner_steps = [];
                }
                this.manufacturer.removed_inner_steps.push(payment.id);
                let index = this.manufacturer.inner_payments.indexOf(payment);
                this.manufacturer.inner_payments.splice(index, 1);
            }
        },
        add_spec(){
            let newSpec = {
                id: 0,
                number: '',
                file: null,
                file_name: null,
                legal_entity_contract_id: null,
                from_db: false,
                delivery_conditions: 0,
                additional_contracts: [],
            };
            this.manufacturer.inner_specifications.push(newSpec);
        },
        remove_spec(spec){
            if (confirm(this.$root.$t("project.Sure_remove_specification"))){
                if (!('removed_inner_specifications' in this.manufacturer)){
                    this.manufacturer.removed_inner_specifications = [];
                }
                this.manufacturer.removed_inner_specifications.push(spec.id);
                let index = this.manufacturer.inner_specifications.indexOf(spec);
                this.manufacturer.inner_specifications.splice(index, 1);
            }
        },
        remove_specification_file(spec){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                spec.file = null;
                spec.file_name = null;
            }
        },
        select_first_inner_spec(spec){
            if (!spec.from_db && spec.legal_entity_contract_id == null){
                this.sync_inner_spec_db(spec);
            }
        },
        sync_inner_spec_db(spec){
            if (this.inner_seller_legal_entity.contracts.length > 0) {
                spec.legal_entity_contract_id = this.inner_seller_legal_entity.contracts[0].id;
            } else {
                spec.legal_entity_contract_id = null;
            }
        },
        //
        add_addit_contract(spec){
            let newAddit = {
                id: 0,
                number: '',
                file: null,
                file_name: null,
                from_db: false,
                legal_entity_contract_id: null,
            };
            spec.additional_contracts.push(newAddit);
        },
        remove_addit_contract(spec, contract){
            if (confirm(this.$root.$t("project.Sure_remove_addit_contract"))){
                if (!('removed_addit_contracts' in spec)){
                    spec.removed_addit_contracts = [];
                }
                spec.removed_addit_contracts.push(contract.id);
                let index = spec.additional_contracts.indexOf(contract);
                spec.additional_contracts.splice(index, 1);
            }
        },
        remove_addit_contract_file(contract){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                contract.file = null;
                contract.file_name = null;
            }
        },
    },
}
</script>
