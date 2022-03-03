<style>
    .common-container{
        display: grid;
        grid-template-columns: repeat(4,23.2%);
        grid-column-gap: 2rem;
    }
    @media (max-width: 1250px) {
        .common-container{
            grid-template-columns: repeat(4,23%);
            grid-column-gap: 1.5rem;
        }
    }
    @media (max-width: 760px) {
        .common-container{
            grid-template-columns: 100%;
            grid-row-gap: 1rem;
        }
    }
</style>

<template lang="pug">
    div
        div.row.mb-2
            div.col-6(v-if="$root.can_with_project('read', 0)")
                div.form-group.row
                    label.col-6.input-line {{ $t('project.Manufacturer') }}
                    div.col-6
                        input.form-control(type="text", v-model="manufacturer.manufacturer.name" readonly)
                div.form-group.row
                    label.col-6.input-line {{ $t('project.Order_number') }}
                    div.col-6
                        input.form-control(type="text", v-model="manufacturer.order_number", :disabled="project.finished || !$root.can_with_project('update', 0)")
            div.col-6(v-if="$root.can_with_project('read',0)")
                h6 {{ $t('project.Comment') }}
                textarea.form-control(v-model="manufacturer.comment_main", :disabled="project.finished || !$root.can_with_project('update', 0)")
            div.col-12
                div.form-group.row
                    label.col-3.input-line {{ $t('project.Order_agreed_with_tech_doc') }}
                    div.col-5.text-nowrap.d-flex(v-if="$root.can_with_project('read', 7)")
                        span(v-on:click="toogle_order_agreed()" style="width: 80px;")
                            bootstrap-toggle(v-model="manufacturer.order_agreed", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 0) || !$root.can_with_project('update', 7)")
                        date-picker.mx-2(:disabled="!manufacturer.order_agreed || project.finished || !$root.can_with_project('update', 0) || !$root.can_with_project('update', 7)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="manufacturer.order_agreed_at", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                        file-uploader(
                            :file_url="manufacturer.order_agreed_file"
                            :file_name="manufacturer.order_agreed_file_name"
                            :editable="manufacturer.order_agreed && !project.finished && $root.can_with_project('update', 0) && $root.can_with_project('update', 7)"
                            @remove="remove_order_agreed"
                            @finished="(arr) => { [manufacturer.order_agreed_file, manufacturer.order_agreed_file_name] = arr }")
                        //select.form-control.mr-2(style="flex:1;", v-model="manufacturer.order_agreed_contract_id", :disabled="project.finished || !$root.can_with_project('update', 7)")
                            option(:value="0") {{ $t('project.Not_selected') }}
                            option(v-for="contract in manufacturer.manufacturer.contracts", :value="contract.id") {{ contract.name }}
                        //div.mr-2(v-if="manufacturer.order_agreed_contract_id" style="flex:1;")
                            a.input-line.short-link(:href="manufacturer_contract(manufacturer.order_agreed_contract_id).file" target="_blank", :title="manufacturer_contract(manufacturer.order_agreed_contract_id).file_name") {{ manufacturer_contract(manufacturer.order_agreed_contract_id).file_name }}
                div.form-group.row
                    label.col-3.input-line {{ $t('project.Order_sent') }}
                    div.col-5.text-nowrap.d-flex
                        span(v-on:click="toogle_order_sent()" style="width: 80px;")
                            bootstrap-toggle(v-model="manufacturer.order_sent", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 0)")
                        date-picker.mx-2(:disabled="!manufacturer.order_sent || project.finished || !$root.can_with_project('update', 0)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="manufacturer.order_sent_at", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                        file-uploader(
                            :file_url="manufacturer.order_sent_file"
                            :file_name="manufacturer.order_sent_file_name"
                            :editable="manufacturer.order_sent && !project.finished && $root.can_with_project('update', 0)"
                            @remove="remove_order_sent"
                            @finished="(arr) => { [manufacturer.order_sent_file, manufacturer.order_sent_file_name] = arr }")
                        //select.form-control.mr-2(style="flex:1;", v-model="manufacturer.order_sent_contract_id", :disabled="project.finished || !$root.can_with_project('update', 7)")
                            option(:value="0") {{ $t('project.Not_selected') }}
                            option(v-for="contract in manufacturer.manufacturer.contracts", :value="contract.id") {{ contract.name }}
                        //div.mr-2(v-if="manufacturer.order_sent_contract_id" style="flex:1;")
                            a.input-line.short-link(:href="manufacturer_contract(manufacturer.order_sent_contract_id).file" target="_blank", :title="manufacturer_contract(manufacturer.order_sent_contract_id).file_name") {{ manufacturer_contract(manufacturer.order_sent_contract_id).file_name }}
                div.row
                    label.col-3.input-line {{ $t('project.Order_confirmed') }}
                    div.col-5.text-nowrap.d-flex
                        span(v-on:click="toogle_order_confirmed()" style="width: 80px;")
                            bootstrap-toggle(v-model="manufacturer.order_confirmed", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 0)")
                        date-picker.mx-2(:disabled="!manufacturer.order_confirmed || project.finished || !$root.can_with_project('update', 0)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="manufacturer.order_confirmed_at", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                        file-uploader(
                            :file_url="manufacturer.order_confirmed_file"
                            :file_name="manufacturer.order_confirmed_file_name"
                            :editable="manufacturer.order_confirmed && !project.finished && $root.can_with_project('update', 0)"
                            @remove="remove_order_confirmed"
                            @finished="(arr) => { [manufacturer.order_confirmed_file, manufacturer.order_confirmed_file_name] = arr }")
                        //select.form-control.mr-2(style="flex:1;", v-model="manufacturer.order_confirmed_contract_id", :disabled="project.finished || !$root.can_with_project('update', 7)")
                            option(:value="0") {{ $t('project.Not_selected') }}
                            option(v-for="contract in manufacturer.manufacturer.contracts", :value="contract.id") {{ contract.name }}
                        //div.mr-2(v-if="manufacturer.order_confirmed_contract_id" style="flex:1;")
                            a.input-line.short-link(:href="manufacturer_contract(manufacturer.order_confirmed_contract_id).file" target="_blank", :title="manufacturer_contract(manufacturer.order_confirmed_contract_id).file_name") {{ manufacturer_contract(manufacturer.order_confirmed_contract_id).file_name }}
        div.mb-2(v-if="$root.can_with_project('read',0)")
            div.project-section {{ $t('project.Shipping_limit') }}
            div.common-container
                div
                    h6 {{ $t('project.Date_forming_type') }}
                    select.form-control(style="display: inline-block;min-width: 150px;flex:1;" v-model="manufacturer.limit_forming_type", :disabled="project.finished || !$root.can_with_project('update', 0)")
                        option(:value="0") {{ $t('project.Amount_days') }}
                        option(:value="1") {{ $t('project.Before_date') }}
                div(v-if="manufacturer.limit_forming_type == 0")
                    h6 {{ $t('project.Date') }}
                    select.form-control(style="display: inline-block;min-width: 150px;flex:1; margin: 0 0 1.5rem 0" v-model="manufacturer.limit_forming_date", :disabled="project.finished || !$root.can_with_project('update', 0)")
                        option(:value="0") {{ $t('project.Date_of_prepayment') }}
                        option(:value="1") {{ $t('project.Date_of_order_confirming') }}
                    h6 {{ $t('project.Days') }}
                    input.form-control(v-model="manufacturer.limit_forming_days" type="number" min="1" style="margin: 0 0 1.5rem 0", :disabled="project.finished || !$root.can_with_project('update', 0)")
                    div(v-if="manufacturer.limit_forming_date == 0")
                        div(v-if="manufacturer.payments[0] && manufacturer.payments[0].payment_date")
                            div {{ $t('project.Date_of_prepayment') }} : {{ manufacturer_prepayment_date }}
                            div {{ $t('project.Shipping_date') }}: {{ limit_manufacturer_prepayment_date_final }}
                        div(v-else) {{ $t('project.Date_of_prepayment') }}: {{ $t('project.Not_set') }}
                    div(v-if="manufacturer.limit_forming_date == 1")
                        div(v-if="manufacturer.order_confirmed_at")
                            div {{ $t('project.Date_of_order_confirming') }} : {{ order_confirmed_at_formatted }}
                            div {{ $t('project.Shipping_date') }}: {{ limit_order_confirmed_at_final }}
                        div(v-else) {{ $t('project.Date_of_order_confirming') }}: {{ $t('project.Not_set') }}
                div(v-if="manufacturer.limit_forming_type == 1")
                    h6 {{ $t('project.Date') }}
                    date-picker(:lang="$root.locale" v-model="manufacturer.limit_before_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", format="DD.MM.YYYY", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 0)")
                div
                    h6 {{ $t('project.Comment') }}
                    textarea.form-control(rows="5" cols="50" v-model="manufacturer.comment_limits", :disabled="project.finished || !$root.can_with_project('update', 0)")
</template>

<script>
import moment from 'moment';

export default {
    props: {
        project: Object,
        manufacturer: Object,
    },
    data: function(){
        return {
            //
        }
    },
    computed: {
        manufacturer_prepayment_date(){
            return moment(this.manufacturer.payments[0].payment_date).format('DD.MM.YYYY');
        },
        limit_manufacturer_prepayment_date_final(){
            return moment(this.manufacturer.payments[0].payment_date).add(this.manufacturer.limit_forming_days, 'days').format('DD.MM.YYYY');
        },
        order_confirmed_at_formatted(){
            return moment(this.manufacturer.order_confirmed_at).format('DD.MM.YYYY');
        },
        limit_order_confirmed_at_final(){
            return moment(this.manufacturer.order_confirmed_at).add(this.manufacturer.limit_forming_days, 'days').format('DD.MM.YYYY');
        },
    },
    methods: {
        manufacturer_contract(contract_id){
            return this.manufacturer.manufacturer.contracts.find(c => c.id == contract_id);
        },
        toogle_order_agreed(){
            if (!this.manufacturer.order_agreed && this.manufacturer.order_agreed_at == null && !this.project.finished){
                this.manufacturer.order_agreed_at = moment();
            }
        },
        toogle_order_sent(){
            if (!this.manufacturer.order_sent && this.manufacturer.order_sent_at == null && !this.project.finished){
                this.manufacturer.order_sent_at = moment();
            }
        },
        toogle_order_confirmed(){
            if (!this.manufacturer.order_confirmed && this.manufacturer.order_confirmed_at == null && !this.project.finished){
                this.manufacturer.order_confirmed_at = moment();
            }
        },
        remove_order_agreed(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.manufacturer.order_agreed_file = null;
                this.manufacturer.order_agreed_file_name = null;
            }
        },
        remove_order_sent(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.manufacturer.order_sent_file = null;
                this.manufacturer.order_sent_file_name = null;
            }
        },
        remove_order_confirmed(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.manufacturer.order_confirmed_file = null;
                this.manufacturer.order_confirmed_file_name = null;
            }
        },
    },
}
</script>