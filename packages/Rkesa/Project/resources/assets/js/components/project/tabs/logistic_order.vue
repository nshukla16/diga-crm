<style>

</style>

<template lang="pug">
    div.mb-2(v-if="currentOrder")
        div.project-section(v-if="$root.can_do('shipping_orders', 'read')") {{ $t('project.Manufacturer_order') + ': ' + manufacturer.manufacturer.name }}
        div.row
            div.col-12.col-lg-8.offset-lg-2(v-if="$root.can_do('shipping_orders', 'read')")
                div.form-group.row(:class="{ 'has-error': errors.has('name') }")
                    label.col-sm-3.col-form-label {{ $t('project.Order_name') }}
                    div.col-sm-9
                        input.form-control(:disabled="project.finished || !$root.can_do('shipping_orders', 'update')", v-validate="'required'", v-bind:data-vv-as="$t('project.Order_name').toLowerCase()", v-model="currentOrder.name", name="name")
                        span.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
                template(v-if="currentOrder.id == 0")
                    div.form-group.row
                        label.col-sm-3.col-form-label {{ $t('project.Request_number') }}
                        div.col-sm-9
                            input.form-control(disabled :value="$t('project.Number_will_be_generated_automatically')")
                template(v-else)
                    div.form-group.row(:class="{ 'has-error': errors.has('number') }")
                        label.col-sm-3.col-form-label {{ $t('project.Request_number') }}
                        div.col-sm-9
                            input.form-control(:disabled="project.finished || !$root.can_do('shipping_orders', 'update')", v-validate="'required'", v-bind:data-vv-as="$t('project.Order_number').toLowerCase()", v-model="currentOrder.number", name="number")
                            span.help-block(v-show="errors.has('number')") {{ errors.first('number') }}
                div.form-group.row(:class="{ 'has-error': errors.has('order_date') }")
                    label.col-sm-3.col-form-label {{ $t('project.Order_date') }}
                    div.col-sm-9
                        date-picker(:disabled="project.finished || !$root.can_do('shipping_orders', 'update')", :first-day-of-week="$root.global_settings.first_day_of_week", v-validate="'required'", v-bind:data-vv-as="$t('project.Order_date').toLowerCase()", :lang="$root.locale" v-model="currentOrder.order_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="'100%'" name="order_date")
                        span.help-block(v-show="errors.has('order_date')") {{ errors.first('order_date') }}
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Manager') }}
                    div.col-sm-9
                        div.mb-2.d-flex(v-for="manager in currentOrder.managers")
                            select.form-control(v-model="manager.user_id", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                                option(v-for="user in users", :value="user.id") {{ user.name }}
                            button.btn.btn-danger.ml-2(v-if="!project.finished && $root.can_do('shipping_orders', 'update')" v-on:click="remove_manager(manager)") {{ $t('template.Remove') }}
                        button.btn.btn-diga(v-if="!project.finished && $root.can_do('shipping_orders', 'update')" v-on:click="add_manager") {{ $t('template.Add') }}
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Sender_company_name') }}
                    div.col-sm-9
                        select.form-control(v-model="currentOrder.sender_manufacturer_id", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                            option(v-for="man in project.manufacturers", :value="man.manufacturer_id") {{ man.manufacturer.name }}
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Sender_legal_address') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.sender_legal_address", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Uploading_address') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.uploading_address", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Manufacturer_contact_name') }}
                    div.col-sm-9.d-flex
                        v-select.w-100.mr-2(v-if="from_bd"
                            v-model='currentOrder.manufacturer_contact_name',
                            :debounce='250',
                            :on-change='contact_select',
                            :on-search='get_contact_options',
                            :options='curr_contact_list',
                            label="name",
                            v-bind:placeholder="$t('estimate.Escolha_a_opcao')")
                            template(slot="no-options") {{ $t('template.No_matching_options') }}
                        input.form-control(v-else v-model="currentOrder.manufacturer_contact_name", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                        button.btn.btn-diga.ml-2(v-if="!project.finished && $root.can_do('shipping_orders', 'update')" v-on:click="from_bd_toggle()") {{ from_bd ? $t('project.Enter_manually') : $t('project.Choose_from_db') }}
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Manufacturer_contact_phone') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.manufacturer_contact_phone", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Manufacturer_contact_email') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.manufacturer_contact_email", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row(:class="{ 'has-error': errors.has('loading_ready_date') }")
                    label.col-sm-3.col-form-label {{ $t('project.Loading_ready_date') }}
                    div.col-sm-9
                        date-picker(:disabled="project.finished || !$root.can_do('shipping_orders', 'update')", :first-day-of-week="$root.global_settings.first_day_of_week", v-validate="'required'", v-bind:data-vv-as="$t('project.Loading_ready_date').toLowerCase()", :lang="$root.locale" v-model="currentOrder.loading_ready_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="'100%'" name="loading_ready_date")
                        span.help-block(v-show="errors.has('loading_ready_date')") {{ errors.first('loading_ready_date') }}
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Sender_contract_number') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.order_contract_and_specifications")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Order_conditions_of_delivery') }}
                    div.col-sm-9
                        select.form-control(:disabled="project.finished || !$root.can_do('shipping_orders', 'update')" v-model="currentOrder.conditions_of_delivery")
                            option(:value="i" v-for="(v,i) in conditions") {{ v }}
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Additional_loading') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.additional_loading", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Shipment_place') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.shipment_place", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Destination_place') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.destination_place", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Shipment_type_and_counts') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.shipment_type_and_counts", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Consignment_receiver_company_name') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.consignment_receiver_company_name", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Consignment_receiver_address') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.consignment_receiver_address", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Consignment_receiver_phone') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.consignment_receiver_phone", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Final_buyer') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.final_buyer", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Client_contract_number') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.client_contract_number", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Downloading_address') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.downloading_address", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Downloading_contact') }}
                    div.col-sm-9
                        select.form-control(v-model="currentOrder.downloading_contact_id", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                            option(v-for="contact in project.client.client_contacts", :value="contact.id") {{ $root.fullName(contact) }}
                        //input.form-control(v-model="currentOrder.downloading_contact_phone", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Order_manufacturer_name') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.manufacturer_name", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Manufacturer_legal_address') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.manufacturer_legal_address", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Loading_name') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.loading_name", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Loading_size') }}
                    div.col-sm-9
                        div.mb-2.d-flex(v-for="place in currentOrder.places")
                            input.form-control(v-model="place.text", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                            button.btn.btn-danger.ml-2(v-if="!project.finished && $root.can_do('shipping_orders', 'update')" v-on:click="remove_place(place)") {{ $t('template.Remove') }}
                        button.btn.btn-diga(v-if="!project.finished && $root.can_do('shipping_orders', 'update')" v-on:click="add_place") {{ $t('template.Add') }}
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Loading_selling_price') }}
                    div.col-sm-9
                        vue-numeric.form-control(v-model="currentOrder.loading_selling_price", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Loading_cost_price') }}
                    div.col-sm-9
                        vue-numeric.form-control(v-model="currentOrder.loading_cost_price", separator=",", v-bind:precision="2", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
                div.form-group.row
                    label.col-sm-3.col-form-label {{ $t('project.Inner_order_name') }}
                    div.col-sm-9
                        input.form-control(v-model="currentOrder.inner_specification_number", :disabled="project.finished || !$root.can_do('shipping_orders', 'update')")
</template>

<script>
import {mapGetters} from "vuex";
import moment from 'moment';
import {shipping_order_conditions_of_delivery} from "../../../helper";

export default {
    inject: ['$validator'],
    props: {
        project: Object,
        manufacturer: Object,
        currentOrder: Object,
    },
    data: function () {
        return {
            from_bd: false,
            curr_contact_list: [],
        }
    },
    computed: {
        ...mapGetters({
            users: 'getNotRemovedUsers',
        }),
        conditions(){
            return shipping_order_conditions_of_delivery;
        },
    },
    mounted(){
        //            if (this.project.manufacturers.length == 1 && this.project.manufacturers[0].order){
        //                this.open_order(this.project.manufacturers[0]);
        //            }

    },
    methods: {
        add_manager(){
            let newManager = {
                id: null,
                user_id: this.users[0].id,
            };
            this.currentOrder.managers.push(newManager);
        },
        remove_manager(man){
            if (confirm(this.$root.$t("project.Sure_remove_manager"))){
                if (!('removed_managers' in this.currentOrder)){
                    this.currentOrder.removed_managers = [];
                }
                this.currentOrder.removed_managers.push(man.id);
                let index = this.currentOrder.managers.indexOf(man);
                this.currentOrder.managers.splice(index, 1);
            }
        },
        add_place(){
            let newPlace = {
                id: null,
                text: '',
            };
            this.currentOrder.places.push(newPlace);
        },
        remove_place(place){
            if (confirm(this.$root.$t("project.Sure_remove_place"))){
                if (!('removed_places' in this.currentOrder)){
                    this.currentOrder.removed_places = [];
                }
                this.currentOrder.removed_places.push(place.id);
                let index = this.currentOrder.places.indexOf(place);
                this.currentOrder.places.splice(index, 1);
            }
        },
        get_contact_options(search, loading) {
            loading(true);
            this.$http.get('/api/manufacturer_contacts?limit=9999&search=' + search + '&manufacturer_id' + this.manufacturer.id).then(res => {
                this.curr_contact_list = res.data.rows;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                loading(false);
            })
        },
        contact_select(val){
            if (typeof val != "string") {
                if (val !== null) {
                    this.currentOrder.manufacturer_contact_name = val.name;
                    this.currentOrder.manufacturer_contact_phone = val.phone;
                    this.currentOrder.manufacturer_contact_email = val.email;
                    this.from_bd = false;
                } else {
                    this.currentOrder.manufacturer_contact_name = '';
                    this.currentOrder.manufacturer_contact_phone = '';
                    this.currentOrder.manufacturer_contact_email = '';
                }
            }
        },
        from_bd_toggle(){
            if (this.from_bd){
                this.from_bd = false;
            } else {
                this.currentOrder.manufacturer_contact_name = '';
                this.currentOrder.manufacturer_contact_phone = '';
                this.currentOrder.manufacturer_contact_email = '';
                this.from_bd = true;
            }
        },
        back_to_list(){
            this.$emit('back_to_list');
        },
    },
}
</script>