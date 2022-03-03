<style>
    span.inactive {color: grey; text-decoration: line-through;}
</style>

<template lang="pug">
div.diga-container.p-4
    h3 {{$t('template.Payments')}}
    div.row
        div.col-md-9
            //- table.table.table-striped
            datatable.table.table-striped(v-bind="table")
                //- thead
                //-     tr
                //-         th №
                //-         th {{$t('template.Module_name')}}
                //-         //- th {{$t('template.trial_period')}}
                //-         th {{$t('template.subscription_period')}}
                //-         th {{$t('template.Module_status')}}
                //- tbody
                //-     tr(v-for="(mod, index) in modules.filter(m => m.trial_date_start || m.current_subscription_date_start)")
                //-         td {{index + 1}}
                //-         td {{$t('template.module-'+mod.name+'-desc')}}
                //-         //- td
                //-         //-     span(v-if="mod.trial_date_start", :class="{ inactive: isBiggerThanNow(mod.trial_date_end) }") {{$t('template.trial_period')}}: {{dateFormat(mod.trial_date_start)}} - {{dateFormat(mod.trial_date_end)}}
                //-         td
                //-             span(v-if="mod.current_subscription_date_start", :class="{ inactive: isBiggerThanNow(mod.current_subscription_date_end) }") {{$t('template.subscription_period')}}: {{dateFormat(mod.current_subscription_date_start)}} - {{dateFormat(mod.current_subscription_date_end)}}
                //-         td
                //-             div.alert.alert-danger(role="alert" v-if="(!mod.trial_date_end || isBiggerThanNow(mod.trial_date_end)) && (!mod.current_subscription_date_end || isBiggerThanNow(mod.current_subscription_date_end))") {{$t('template.Module_not_active')}}
                //-             div.alert.alert-success(role="alert" v-if="(mod.trial_date_end && !isBiggerThanNow(mod.trial_date_end)) || (mod.current_subscription_date_end && !isBiggerThanNow(mod.current_subscription_date_end))") {{$t('template.Module_active')}}
        div.col-md-3.text-center
            p {{$t('template.Number_of_workers')}}
            h1 {{globalSettings.settings.max_users}}
            p {{$t('template.Your_current_balance')}}
            h1 {{globalSettings.settings.company_balance}} {{format_currency(globalSettings.settings.price_currency)}}

    div.text-center
        router-link.btn.btn-diga(:to="{ name: 'payments' }") {{ $t('template.Change_conditions') }}

 
</template>

<script>
import date_column from './custom_columns/td_date_created.vue';
import status_column from './custom_columns/td_status.vue';
import shopping_cart_column from './custom_columns/td_shopping_cart.vue';
import sum_column from './custom_columns/td_sum.vue';
import invoice_column from './custom_columns/td_invoice.vue';
import number_of_workers_column from './custom_columns/td_number_of_workers.vue';
import {mapGetters} from "vuex";
import moment from 'moment';

export default {
    data() {
        return {
            modules: [],
            table: {
                columns: [
                    { title: this.$root.$t('template.History_created'), tdComp: date_column },
                    { title: this.$root.$t('template.Module_status'), tdComp: status_column },
                    { title: this.$root.$t('template.shopping_cart'), tdComp: shopping_cart_column },
                    { title: this.$root.$t('template.total_sum'), tdComp: sum_column, field: 'sum', sortable: true },
                    { title: this.$root.$t('template.invoice'), tdComp: invoice_column },
                    { title: this.$root.$t('template.Number_of_workers'), tdComp: number_of_workers_column }
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
        }
    },
    props: ['offset'],
    computed: {
    },
    methods: {
        format_currency(currency){
            return {
                'eur': '€',
                'rub': '₽',
            }[currency];
        },
        getModules(){
            this.$http.get('/api/modules').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.modules = res.data;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
        getPayments(){
            this.$http.get('/api/payments/paging').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.table.data = res.data.rows;
                    this.table.total = res.data.total;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
        dateFormat(datetime) {
            return moment(datetime).format('D MMM YYYY');
        },
        isBiggerThanNow(datetime){
            return moment() >= moment(datetime);
        },
    },
    created(){
        this.$bus.$on("get_payments", this.getPayments);
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.Payments');
        this.getModules();
        this.getPayments();
    },
    beforeDestroy: function() {
        this.getPayments && this.$bus.$off("get_payments", this.getPayments);
    },
    computed:{
        ...mapGetters({
            globalSettings: 'getGlobalSettings',
        }),
    },
    watch: {
        'table.query': {
            handler(query) {
                this.getPayments();
            },
            deep: true,
        },
        search(value){
            this.getPayments();
        }
    },
}
</script>