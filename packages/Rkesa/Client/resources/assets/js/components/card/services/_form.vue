<style>

</style>

<template lang="pug">
    div#service-page(v-if="my_contact")
        client(v-if="$root.enable_companies && my_contact.client", :client="my_contact.client")
        contact(:contact="my_contact")
        .row.clearfix
            services.col-12.mb-3(:class="{'col-xl-4': !services_maximized}",
                :services="my_contact.services",
                :main_contact_id="my_contact.id",
                :editable="false",
                :maximized="services_maximized",
                @maximize_minimize_click="maximize_minimize_click")
            div.col-12.mb-3(v-if="currentService", :class="{'col-md-8': $root.global_settings.disable_service_address === true && !services_maximized, 'col-md-6 col-xl-4': !services_maximized && $root.global_settings.disable_service_address !== true, 'col-md-6': services_maximized}")
                div.diga-container.p-3
                    .profile-content
                        .caption.caption-md
                            span.caption-subject.font-blue-madison.bold.uppercase {{ currentService.id ? $t("client.Edit_service")+': '+$root.service_number(currentService) : $t("client.New_service") }}
                        fieldset.form-group(:class="{ 'has-error': errors.has('contact') }")
                            label.control-label {{ $t("client.Contact") }}
                            v-select(:debounce='250', :on-search='get_contacts_options', :class="{ danger: errors.has('contact') }", v-bind:data-vv-as="$t('client.Contact').toLowerCase()", v-validate:selected="'required'", name="contact", :on-change='contact_select', v-model="selected", :options='contacts', :placeholder="$t('client.Choose_contact')")
                                template(slot="no-options") {{ $t('template.No_matching_options') }}
                            span.help-block(v-show="errors.has('contact')") {{ errors.first('contact') }}
                        fieldset.form-group
                            label.control-label {{ $t("client.Caption") }}
                            input.form-control(v-bind:placeholder="$t('client.Caption')", type="text", v-model="currentService.name")
                        .row
                            fieldset.form-group.col-6
                                label.control-label {{ $t("client.Service_type") }}
                                select.form-control(v-model="currentService.service_type_id" @change="on_service_type_select($event)")
                                    option(disabled value="0") {{ $t("client.Choose_service_type") }}
                                    option(v-for="type in service_types" v-bind:value="type.id") {{ type.name }}
                            fieldset.form-group.col-6
                                label.control-label {{ $t("client.Priority") }}
                                select.form-control(v-model="currentService.service_priority_id")
                                    option(disabled value="0") {{ $t("client.Choose_priority") }}
                                    option(v-for="priority in priorities" v-bind:value="priority.id") {{ priority.name }}
                        .row
                            fieldset.form-group.col-6
                                label.control-label {{ $t("client.Responsible") }}
                                select.form-control(v-model="currentService.responsible_user_id")
                                    option(disabled value="0") {{ $t("client.Choose_user") }}
                                    option(v-for="user in users" v-bind:value="user.id") {{ user.name }}
                            fieldset.form-group.col-6(:class="{ 'has-error': errors.has('state') }")
                                label.control-label {{ $t("client.state") }}
                                select.form-control(v-model="currentService.service_state_id" name="state" v-validate="'not_in:0'" v-bind:data-vv-as="$t('client.state').toLowerCase()")
                                    option(disabled value="0") {{ $t("client.Choose_state") }}
                                    option(v-for="state in states" v-if="state.type == 0" v-bind:value="state.id") {{ state.name }}
                                span.help-block(v-show="errors.has('state')") {{ errors.first('state') }}
                        fieldset.form-group
                            label.control-label {{ $t("client.Note") }}
                            textarea.form-control(v-bind:placeholder="$t('client.exemplo')" v-model="currentService.note")
                        template(v-if="/*$root.global_settings.company_type == 2 && */$root.user.can_see_prices")
                            fieldset.form-group
                                label.control-label {{ $t("estimate.Preco") }} {{$root.current_currency.symbol}}
                                input.form-control(type="number" step="0.01" min="0" v-model="currentService.estimate_summ")
                            fieldset.form-group
                                label.control-label {{ $t("client.Paid_summ") }} {{$root.current_currency.symbol}}
                                input.form-control(type="number" step="0.01" min="0" v-model="currentService.paid_summ")
                        button.btn.btn-diga(v-on:click="create_service") {{ $t("template.Save") }}
                        button.btn.btn-danger.float-right(v-if="!isCreating && $root.can_with_service('delete', currentService)" v-on:click="remove_service()") {{ $t("template.Remove") }}
                        router-link.btn.btn-default.float-right(:to="{name: this.$root.contact_or_client_show_route(), params: {id: currentService.client_contact_id }}") {{ $t("template.Cancel") }}
            div.col-12.mb-3(v-if="currentService && $root.global_settings.disable_service_address !== true", :class="{'col-md-6 col-xl-4': !services_maximized, 'col-md-6': services_maximized}")
                div.diga-container.p-3
                    .profile-content
                        .caption.caption-md
                            span.caption-subject.font-blue-madison.bold.uppercase {{ $t("client.Morada") }}
                        fieldset.form-group(:class="{ 'has-error': errors.has('address') }")
                            gmap-autocomplete(v-if="!currentService.autocomplete_disabled", @place_changed="setPlace", class="form-control" :value="autocomplete.address")
                            input.form-control(v-else v-bind:placeholder="$t('client.Morada')", type="text", v-model="currentService.address")
                            span.help-block(v-show="errors.has('address')") {{ errors.first('address') }}
                            input(v-if="!currentService.autocomplete_disabled", type="hidden", v-model="currentService.address", name="address", v-validate="'required'")
                            .form-check
                                label.form-check-label
                                    input.form-check-input(type="checkbox", v-model="currentService.autocomplete_disabled")
                                    | &nbsp {{ $t("client.DisableGoogleAutocomplete") }}
                            .table-responsive(v-if="$root.settings.show_aru == '1'")
                                table.table.table-bordered
                                    thead
                                        tr
                                            th {{ $t("client.Municipal") }}
                                            th {{ $t("client.Zone") }}
                                            th {{ $t("client.Discount") }}
                                            th {{ $t("client.Address_to_save") }}
                                    tbody
                                        tr
                                            td(v-text="location.municipal")
                                            td(v-text="location.zone")
                                            td(v-text="location.discount")
                                            td(v-text="currentService.address")
                            gmap-map(:center="google.map.position", :zoom="google.map.zoom", map-type-id="terrain", style="height: 300px", ref="map")
                                gmap-marker(:position="google.marker.position")
</template>

<script>
import contact from '../../shared/contact.vue'
import client from '../../shared/client.vue'
import services from './index.vue'
import history from '../history.vue'

import { mapGetters } from 'vuex';

// probably better to avoid using a lot of third party libs inside of vue container (optional)
let geocoder;
let layer; // object constructor (new google.maps.Data() )

export default {
    props: ['from', 'id', 'contact_id'],
    components: {
        contact, client, services, history,
    },
    data: function(){
        return {
            isCreating: true,
            currentService: null, // newCompany or existed company loaded from api

            services_maximized: false,
            my_contact: null,

            // service form
            selected: null,
            contacts: [], // options
            // service vars for google autocomplete, geojson
            google: {
                map: {
                    zoom: 17,
                    position: {lat: 38.743625, lng: -9.154922},
                },
                marker: {
                    position: {lat: 38.743625, lng: -9.154922},
                },
                polygons: [], // geo collection here,
            },
            // info table for places autocomplete input
            location: {
                discount: 'n/a',
                municipal: 'n/a',
                zone: 'n/a',
            },
            autocomplete: {
                address: null,
            },
        }
    },
    // TODO in some feature need to make 'loading' state of ARU infowindow for better user expiriense
    methods: {
        on_service_type_select(event){
            if (event.target.value !== null && event.target.value > 0){
                var service_type = this.service_types.find(s => s.id === parseInt(event.target.value));
                if (service_type != null && (this.currentService.name === null || this.currentService.name === "")){
                    this.currentService.name = service_type.name;
                }
                if (service_type != null && (!this.currentService.estimate_summ || this.currentService.estimate_summ === null || this.currentService.estimate_summ === 0)){
                    this.currentService.estimate_summ = service_type.price;
                }
            } 
        },
        load_contact: function(){
            let $this = this;
            this.$root.global_loading = true;
            return new Promise((resolve, reject) => {
                if (this.contact_id) {
                    this.$http.get('/api/contacts/' + this.contact_id).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                            this.$root.global_loading = false;
                            reject();
                        } else {
                            this.my_contact = res.data;
                            this.my_contact.services.forEach(function (service, i) {
                                $this.my_contact.services[i].client_contact = $this.my_contact;
                            });
                            this.my_contact.events.forEach(function (event, i) {
                                $this.my_contact.events[i].client_contact = $this.my_contact;
                            });
                            this.selected = {
                                'label': this.$root.fullName(this.my_contact),
                                'value': this.my_contact.id,
                            };
                            this.$root.global_loading = false;
                            resolve();
                        }
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                        reject();
                    });
                } else {
                    this.$toastr.e(this.$root.$t("client.Client_id_not_found"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                    reject();
                }
            });
        },
        load_service: function(){
            this.$root.global_loading = true;
            return new Promise((resolve, reject) => {
                this.$http.get('/api/services/' + this.id).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                        reject();
                    } else {
                        this.currentService = res.data;
                        this.$root.global_loading = false;
                        resolve();
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                    reject();
                });
            });
        },
        maximize_minimize_click(){
            this.services_maximized = !this.services_maximized;
        },
        remove_service(){
            if (confirm(this.$root.$t('client.Are_you_sure_you_want_to_delete_service'))) {
                this.$http.delete('/api/services/' + this.currentService.id).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("client.Service_removed"), this.$root.$t("template.Success"));
                        this.$router.push({name: this.$root.contact_or_client_show_route(), params: {id: this.my_contact.id }})
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                });
            }
        },
        get_contacts_options(search, loading) {
            loading(true);
            this.$http.get('/api' + this.$root.contact_or_client_store() + '?format=json&limit=20&query=' + search).then(res => {
                var processedData = [];
                let $this = this;
                res.data.rows.forEach(function(i){
                    processedData.push({'label': $this.$root.fullName(i), 'value': i.id});
                });
                this.contacts = processedData;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            })
        },
        contact_select(res){
            if (res == null){
                this.currentService.client_contact_id = null;
                this.selected = null;
            } else {
                this.currentService.client_contact_id = res.value;
                this.selected = res;
            }
        },
        // ARU (areas de reabilitacao urbana ) begins
        getAru() {
            this.$http.post('/api/clients/get_aru_feature').then(res => {
                if (res.ok && !res.data.errcode) {
                    // console.log(JSON.stringify(res.data.aru))
                    layer.addGeoJson(res.data.aru);
                    layer.setMap(this.$refs.map.$mapObject);
                    // if address already exists, need to check again
                    if (this.currentService.address && !this.currentService.autocomplete_disabled) {
                        this.getPlaceFromString(this.currentService.address);
                    }
                } else this.$toastr.e(this.$root.$t('client.Trouble_with_getting_ARU'), this.$root.$t("template.Error"));
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },

        getPlaceFromString() {
            let $this = this;
            geocoder = new google.maps.Geocoder();
            geocoder.geocode({address: this.currentService.address}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    $this.setPlace(results[0]);
                } else {
                    $this.$toastr.e($this.$root.$t('client.Cannot_parse_service_address'), $this.$root.$t("template.Error"));
                }
            });
        },

        setPlace(place) {
            if (!place.geometry) {
                this.$toastr.e(this.$root.$t("client.Autocomplete_returned_place_contains_no_geometry"), this.$root.$t("template.Error"));
                return;
            }

            if (!place.address_components) {
                this.$toastr.e(this.$root.$t("client.This_address_has_no_components"), this.$root.$t("template.Error"));
                return;
            }

            // check if current autocomplete place inside of ARU zones..
            var inAru = 0;
            var overlap = 0;
            var data = {};

            for (var i = 0; i < this.google.polygons.length; i++) {
                if (google.maps.geometry.poly.containsLocation(place.geometry.location, this.google.polygons[i])) {
                    if (!this.google.polygons[i].overlap) {
                        inAru++;
                        data.name = this.google.polygons[i].name;
                        data.zone = this.google.polygons[i].desc;
                        data.discount = this.google.polygons[i].discount;
                        data.id = this.google.polygons[i].id;
                    } else {
                        overlap++;
                    }
                }
            }

            this.currentService.address = place.formatted_address;
            this.autocomplete.address = place.formatted_address;
            this.google.map.position = place.geometry.location;
            this.google.marker.position = place.geometry.location;

            if (!overlap && inAru) {
                this.location.municipal = data.name;
                this.location.zone = data.zone;
                this.location.discount = data.discount;
                this.currentService.aru_id = data.id;
            } else {
                this.location.municipal = 'n/a';
                this.location.zone = 'n/a';
                this.location.discount = 'n/a';
            }
        },

        createPolygons(e) {
            var paths = [];

            if (e.feature.getGeometry().getType() == "Polygon") {
                for (var i = 0; i < e.feature.getGeometry().getArray().length; i++) {
                    var path = [];
                    paths.push(e.feature.getGeometry().getAt(i).getArray());
                }
            }

            var config = {
                paths: paths,
                clickable: false,
                id: e.feature.getProperty('id'),
                name: e.feature.getProperty('name'),
                desc: e.feature.getProperty('description'),
                discount: e.feature.getProperty('discount'),
                overlap: e.feature.getProperty('overlap'),
            };

            var polygon = new google.maps.Polygon(config);
            this.google.polygons.push(polygon);
        },
        // ARU (areas de reabilitacao urbana ) ends
        create_service: function() {
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.$root.global_loading = true;
                let payload = this.currentService;
                if (this.isCreating) {
                    this.$http.post('/api/services', payload).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("client.Service_saved"), this.$root.$t("template.Success"));
                            this.$router.push({ name: 'contact_show', params: { id: this.currentService.client_contact_id } });
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                } else { // update
                    this.$http.patch('/api/services/' + this.currentService.id, payload).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("client.Service_saved"), this.$root.$t("template.Success"));
                            if (this.from == 'index') {
                                this.$router.push({ name: 'services_index' });
                            } else {
                                this.$router.push({ name: 'contact_show', params: { id: this.currentService.client_contact_id } });
                            }
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                }
            });
        },
        load_all(){
            this.load_contact().then(async () => {
                if (this.id) {
                    document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('client.Edit_service');
                    await this.load_service();
                    this.isCreating = false;
                } else {
                    document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('client.New_service');
                    let newService = {
                        service_type_id: 0,
                        address: null,
                        service_priority_id: 0,
                        responsible_user_id: 0,
                        service_state_id: this.states[0].id,
                        autocomplete_disabled: false,
                        client_contact_id: this.my_contact.id,
                        name: null,
                        note: null,
                        // aru id for region (if exists)
                        aru_id: null,
                    };
                    this.currentService = Object.assign({}, newService);
                    this.isCreating = true;
                }
                this.autocomplete.address = this.currentService.address;
                if (this.currentService.service_type_id === null){
                    this.currentService.service_type_id = 0;
                }
                if (this.currentService.responsible_user_id === null){
                    this.currentService.responsible_user_id = 0;
                }
                let $this = this;
                this.my_contact.services.forEach(function(service, i){
                    $this.my_contact.services[i].client_contact = $this.my_contact;
                });
                if (this.$root.settings.show_aru == '1' && this.$root.global_settings.disable_service_address !== true) {
                    this.$nextTick(() => {
                        $this.$refs.map.$mapPromise.then((map) => {
                            layer = new google.maps.Data();
                            layer.addListener('addfeature', $this.createPolygons);
                            $this.getAru();
                        });
                    });
                }
            });
        },
    },
    computed: {
        ...mapGetters({
            priorities: 'getServicePriorities',
            service_types: 'getServiceTypes',
            users: 'getNotRemovedUsers',
            states: 'getNotRemovedServiceStates',
        }),
    },
    mounted(){
        this.load_all();
    },
    watch: {
        '$route' (to, from) {
            this.load_all();
        },
    },
}
</script>