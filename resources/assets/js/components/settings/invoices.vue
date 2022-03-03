<style>
</style>

<template lang="pug">
    div
        div.row
            div.col-12.col-md-6.mb-3(v-if="payment_conditions")
                div.diga-container.p-4
                    h2 {{ $t('template.payment_conditions') }}
                    table.referrers-table
                        tr
                            th #
                            th {{ $t('calendar.Name') }}
                            th {{ $t('template.payment_conditions_days') }}
                        tr(v-for="(payment_condition,i) in payment_conditions" style="margin-bottom: 5px;")
                            td
                                | {{ i+1 }}.
                            td
                                input.form-control(v-model="payment_condition.name")
                            td
                                input.form-control(v-model="payment_condition.days" type="number" step="1" :placeholder="$t('template.payment_conditions_days')")
                            td
                                button.btn.red(v-on:click="remove_payment_condition(payment_condition)") {{ $t('template.Remove') }}
                    button.btn.btn-diga.mt-2(v-on:click="add_payment_condition()") {{ $t('template.Add') }}
            div.col-12.col-md-6.mb-3(v-if="movement_types")
                div.diga-container.p-4
                    h2 {{ $t('template.movement_type') }}
                    table.referrers-table
                        tr
                            th #
                            th {{ $t('calendar.Name') }}
                            th {{ $t('template.Description') }}
                            //- th {{ $t('template.payment_conditions_days') }}
                        tr(v-for="(movement_type,i) in movement_types" style="margin-bottom: 5px;")
                            td
                                | {{ i+1 }}.
                            td
                                input.form-control(v-model="movement_type.name")
                            td
                                input.form-control(v-model="movement_type.description")
                            //- td
                            //-     input.form-control(v-model="movement_type.days" type="number" step="1" :placeholder="$t('template.payment_conditions_days')")
                            td
                                button.btn.red(v-on:click="remove_movement_type(movement_type)") {{ $t('template.Remove') }}
                    button.btn.btn-diga.mt-2(v-on:click="add_movement_type()") {{ $t('template.Add') }}
        div.row
            div.col-12.col-md-6.mb-3
                div.diga-container.p-4
                    h2 {{ $t('template.Note') }}
                    textarea.form-control(v-model="invoice_notes")
            div.col-12.col-md-6.mb-3
                div.diga-container.p-4
                    h2 {{ $t('template.regime_of_exemption_vat') }}
                    label.mr-2 {{ $t('template.use_regime_of_exemption') }}
                    bootstrap-toggle(v-model="always_use_exemption_for_invoices", :options="{ on: $t('client.yes'), off: $t('client.no')}", data-width="120", data-height="30", data-onstyle="default")
                    select.form-control.mt-2(v-if="always_use_exemption_for_invoices" v-model="vat_exemption_reason_id")
                        option(:value="null" selected disabled) {{$t('template.Choose')}}
                        option(v-for="ver in vat_exemption_reasons" :value="ver.id") {{ver.name}}
        div.row
            div.col-12
                button.btn.btn-diga(v-on:click="save_settings()" style="margin-right: 20px;") {{ $t('template.Save') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    data() {
        return {
            invoice_notes: "",
            vat_exemption_reason_id: null,
            always_use_exemption_for_invoices: false,
            payment_conditions: [],
            removed_payment_conditions: [],
            movement_types: [],
            removed_movement_types: [],

            vat_exemption_reasons: [],
        }
    },
    created(){
        this.get_payment_conditions();
        this.get_movement_types();
        this.get_invoice_notes();
        this.get_vat_exemption_reason_id();
        this.get_always_use_exemption_for_invoices();
        this.get_vat_exemption_reasons();
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.invoice_settings');
    },
    computed: {
    },
    methods: {
        get_vat_exemption_reasons(){
            this.$root.global_loading = true;
            this.$http.get('/api/vat_exemption_reasons').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.vat_exemption_reasons = res.data.rows;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });            
        },
        get_always_use_exemption_for_invoices(){
            this.always_use_exemption_for_invoices = this.$root.global_settings.always_use_exemption_for_invoices;
        },
        get_vat_exemption_reason_id(){
            this.vat_exemption_reason_id = this.$root.global_settings.vat_exemption_reason_id;
        },
        get_invoice_notes(){
            this.invoice_notes = this.$root.global_settings.invoice_notes;
        },
        get_payment_conditions(){
            this.$root.global_loading = true;
            this.$http.get('/api/payment_conditions').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.payment_conditions = res.data.rows;
                    if (this.payment_conditions.length > 0){
                        this.payment_condition_id = this.payment_conditions[0].id;
                    }
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });            
        },
        get_movement_types(){
            this.$root.global_loading = true;
            this.$http.get('/api/movement_types').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.movement_types = res.data.rows;
                    if (this.movement_types.length > 0){
                        this.movement_type_id = this.movement_types[0].id;
                    }
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });            
        },
        save_settings(){
            this.$root.global_loading = true;
            let payload = {
                payment_conditions: this.payment_conditions,
                removed_payment_conditions: this.removed_payment_conditions,
                movement_types: this.movement_types,
                removed_movement_types: this.removed_movement_types,
                invoice_notes: this.invoice_notes,
                always_use_exemption_for_invoices: this.always_use_exemption_for_invoices,
                vat_exemption_reason_id: this.vat_exemption_reason_id
            };

            this.$http.post('/api/invoice_settings', payload).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.$toastr.s(this.$root.$t("template.Settings_saved"), this.$root.$t("template.Success"));
                    this.removed_payment_conditions = [];
                    this.removed_movement_types = [];
                    this.get_payment_conditions();
                    this.get_movement_types();
                    this.get_invoice_notes();
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });            
        },
        remove_payment_condition(payment_condition){
            if (confirm(this.$root.$t("calendar.AreYouSure"))){
                this.removed_payment_conditions.push(payment_condition.id);
                let index = this.payment_conditions.indexOf(payment_condition);
                this.payment_conditions.splice(index, 1);
            }
        },
        add_payment_condition(){
            let payment_condition = {
                id: 0,
                name: '',
                days: 0
            };
            this.payment_conditions.push(payment_condition);
        },
        remove_movement_type(movement_type){
            if (confirm(this.$root.$t("calendar.AreYouSure"))){
                this.removed_movement_types.push(movement_type.id);
                let index = this.payment_conditions.indexOf(movement_type);
                this.movement_types.splice(index, 1);
            }
        },
        add_movement_type(){
            let movement_type = {
                id: 0,
                name: '',
                description: '',
                days: 0
            };
            this.movement_types.push(movement_type);
        },
    },
}
</script>