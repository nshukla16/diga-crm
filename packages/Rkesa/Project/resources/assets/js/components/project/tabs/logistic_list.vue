<style>

</style>

<template lang="pug">
    div.mb-2
        template(v-if="currentOrder")
            button.btn.btn-diga.w-100(v-on:click="back_to_list") << {{ $t('project.Back_to_list') }}
            div.project-section(style="margin-bottom:1px;") {{ $t('project.Logistics') + ': ' + manufacturer_opened.manufacturer.name }}
            template(v-if="currentOrder")
                logistic_order(v-if="$root.can_do('shipping_orders', 'read')", :project="project", :manufacturer="manufacturer_opened", :currentOrder="currentOrder", @back_to_list="cancel_order")
                div(v-if="currentOrder.id != 0")
                    logistic_carriers(:project="project", :order="currentOrder")
                    logistic_customs(:project="project", :manufacturer="manufacturer_opened", :order="currentOrder")
                    logistic_transportation_steps(v-if="$root.can_with_project('read', 2)", :project="project", :order="currentOrder")
                    logistic_transportation_vat_steps(v-if="$root.can_with_project('read', 2)", :project="project", :order="currentOrder")
                portal(to="logistic-save-button-destination")
                    div.text-center(v-if="!project.finished && $root.can_do('shipping_orders', 'update')")
                        button.btn.btn-diga(style="margin: 5px;" v-on:click="save_order") {{ currentOrder.id == 0 ? $t('template.Create') : $t('template.Update') }}
        template(v-else)
            div.project-section {{ $t('project.Manufacturer_orders') }}
            table.table
                thead
                    tr
                        th {{ $t('project.Order_name') }}
                        th {{ $t('project.Request_number') }}
                        th {{ $t('project.Manufacturer_name') }}
                        th {{ $t('project.Order_date') }}
                        th {{ $t('project.Transportation_expenses_block') }}
                        th {{ $t('project.Transportation_vat_expenses_block') }}
                        th {{ $t('project.Actions') }}
                tbody(v-if="if_has_order")
                    template(v-for="manu in all_manufacturers")
                        tr(v-for="order in manu.orders")
                            td {{ order.name }}
                            td {{ order.number }}
                            td {{ manu.manufacturer.name }}
                            td {{ format_order_date(order) }}
                            td {{ $root.formatFinanceValue(order.transportation_total) }} {{get_currency_format($root.currencies[$root.global_settings.currency])}}
                                //- {{ project.contract_currency }}
                            td {{ $root.formatFinanceValue(order.transportation_vat_total) }} {{get_currency_format($root.currencies[$root.global_settings.currency])}}
                            td.column_autosize
                                button.btn.btn-diga.mr-2(v-on:click="open_order(order, manu.manufacturer_id)") {{ $t('template.Open') }}
                                button.btn.btn-diga.mr-2(@click="export_results(order.id)") {{ $t('template.Export') }}
                                button.btn.btn-diga.btn-danger(:disabled="project.finished" v-if="$root.can_do('shipping_orders', 'delete')" @click="remove_order(order)") {{ $t('template.Remove') }}
                tbody(v-else)
                    template
                        tr
                            td.text-center(colspan="5") {{ $t('project.No_orders_create_in_relationships') }}
                tfoot
                    tr
                        td(colspan="3")
                        td(style="text-align: right;") {{ $t('project.Transportation_expenses_total') }}:
                        td {{ $root.formatFinanceValue(total_expenses) }} {{get_currency_format(this.$root.currencies[this.$root.global_settings.currency])}}
                            //- {{ project.contract_currency }}
                        td
                    tr
                        td(colspan="3")
                        td(style="text-align: right;") {{ $t('project.Transportation_vat_expenses_total') }}:
                        td {{ $root.formatFinanceValue(total_vat_expenses) }} {{get_currency_format(this.$root.currencies[this.$root.global_settings.currency])}}
                            //- {{ project.contract_currency }}
                        td
</template>

<script>
import moment from 'moment';
import logistic_order from './logistic_order.vue';
import logistic_carriers from './logistic_carriers.vue';
import logistic_customs from './logistic_customs.vue';
import logistic_transportation_steps from './logistic_transportation_steps.vue';
import logistic_transportation_vat_steps from './logistic_transportation_vat_steps.vue';

export default {
    components: {
        logistic_order, logistic_carriers, logistic_customs, logistic_transportation_steps, logistic_transportation_vat_steps,
    },
    props: {
        project: Object,
    },
    data: function () {
        return {
            opened_index: null,
            currentOrder: null,
            show_selected_id: null,
            manufacturer_id: null,
            if_has_order: false,
        }
    },
    computed: {
        manufacturer_opened(){
            let index = this.project.manufacturers.map(function(e) { return e.manufacturer_id; }).indexOf(this.manufacturer_id);
            if (index !== null){
                return this.project.manufacturers[index];
            } else {
                return null;
            }
        },
        all_manufacturers(){
            return this.project.manufacturers;
        },
        total_expenses(){
            let exp = this.all_manufacturers.map(m => m.orders.map(o => o.transportation_total).reduce((a, b) => parseFloat(a) + parseFloat(b), 0)).reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
            this.project.transportation_total = exp;
            return exp;
        },
        total_vat_expenses(){
            let exp = this.all_manufacturers.map(m => m.orders.map(o => o.transportation_vat_total).reduce((a, b) => parseFloat(a) + parseFloat(b), 0)).reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
            this.project.transportation_vat_total = exp;
            return exp;
        },        
    },
    created(){
        for (let i = 0; i < this.project.manufacturers.length; i++){
            if (this.project.manufacturers[i].orders.length !== 0){
                this.if_has_order = true;
            }
        }
        if (this.$route.params.orderId){
            let manufacturer = this.project.manufacturers.find(e => e.manufacturer_id === this.$route.params.ManufacId);
            let order = manufacturer.orders.find(e => e.id === this.$route.params.orderId);
            this.open_order(order, this.$route.params.ManufacId);
        }
    },
    mounted(){
        if (this.project.manufacturers.length == 1){
            this.open(0);
        }
        this.$bus.$on("create_new_order", this.create_new_order);
    },
    beforeDestroy: function() {
        this.create_new_order && this.$bus.$off("create_new_order", this.create_new_order);
    },
    methods: {
        remove_order(order){
            if (confirm(this.$root.$t('project.Are_you_sure_you_want_to_delete_manufacturer_order'))) {
                this.$root.global_loading = true;
                this.$http.delete('/api/manufacturer_orders/' + order.id).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("project.Equipment_removed"), this.$root.$t("template.Success"));
                        let i = this.manufacturer_opened.orders.indexOf(order);
                        this.manufacturer_opened.orders.splice(i, 1);
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                });
            }
        },
        export_results(id){
            this.$root.global_loading = true;
            this.$http.get('/api/manufacturer_orders/export?id=' + id, {responseType: 'blob'}).then(response => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'orders-' + moment().format("DD-MM-YYYY-HH-mm-ss") + '.xlsx');
                document.body.appendChild(link);
                link.click();
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        back_to_list(){
            this.opened_index = null;
            this.currentOrder = null;
            this.$route.params.orderId = null;
        },
        open(i){
            this.opened_index = i;
        },
        open_order(order, id){
            this.currentOrder = JSON.parse(JSON.stringify(order));
            this.manufacturer_id = id;
        },
        create_new_order(manufacturer){
            if (this.project.client.client_contacts === null || this.project.client.client_contacts.length === 0){
                this.$toastr.w(this.$root.$t("template.fill_company_contact"), this.$root.$t("template.Warning"));
                return;
            }
            this.manufacturer_id = manufacturer.manufacturer_id;
            let index = this.project.manufacturers.map(function(e) { return e.manufacturer_id; }).indexOf(manufacturer.manufacturer_id);
            this.open(index);
            let newOrder = {
                id: 0,
                name: '',
                number: '',
                order_date: manufacturer.order_date,
                responsible_user_id: this.$root.user.id,
                sender_manufacturer_id: manufacturer.manufacturer_id,
                sender_legal_address: manufacturer.manufacturer.legal_address,
                conditions_of_delivery: this.project.conditions_of_delivery,
                loading_ready_date: manufacturer.designated_shipping_date,
                order_contract_and_specifications: this.generate_combo_line(manufacturer),
                inner_specification_number: manufacturer.inner_specification_number,
                client_contract_number: this.project.contract_number,
                uploading_address: manufacturer.manufacturer.uploading_address,
                project_manufacturer_id: manufacturer.id,
                project_id: this.project.id,
                manufacturer_legal_address: manufacturer.manufacturer.legal_address,
                loading_selling_price: this.project.contract_price + ' ' + this.$root.currencies[this.project.contract_currency].code,
                managers: [],
                places: [],
                downloading_contact_id: this.project.client.client_contacts[0].id,
                manufacturer_name: manufacturer.manufacturer.name,
                customs_documents: [],
                carriers: [],
            };
            this.currentOrder = newOrder;
        },
        cancel_order(){
            this.currentOrder = null;
        },
        generate_combo_line(m){
            return [
                m.order_number,
                m.contract_number,
            ].concat(m.specifications.map(s => s.number)).join(', ');
        },
        save_order(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }

                this.$root.global_loading = true;
                if (this.currentOrder.id == 0) {
                    this.$http.post('/api/manufacturer_orders/', this.currentOrder).then(async res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Request_saved"), this.$root.$t("template.Success"));

                            let loaded_order = await this.reload_order(res.data.id);
                            this.manufacturer_opened.orders.push(loaded_order);
                            this.currentOrder = JSON.parse(JSON.stringify(loaded_order));
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                } else {
                    this.$http.patch('/api/manufacturer_orders/' + this.currentOrder.id, this.currentOrder).then(async res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Request_saved"), this.$root.$t("template.Success"));

                            let old_order = this.manufacturer_opened.orders.find(o => o.id === this.currentOrder.id);
                            let loaded_order = await this.reload_order(this.currentOrder.id);
                            Object.assign(old_order, loaded_order);
                            this.currentOrder = JSON.parse(JSON.stringify(loaded_order));
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                }
            });
        },
        reload_order(id){
            return new Promise((resolve, reject) => {
                this.$http.get('/api/manufacturer_orders/' + id).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        reject();
                    } else {
                        resolve(res.data);
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    reject();
                });
            });
        },
        format_order_date(order){
            return moment(order.order_date).format('DD.MM.YYYY');
        },
        get_currency_format(currency){
            //                return currency.name + ' (' + currency.code + ')';
            return currency.code;
        },
    },
}
</script>