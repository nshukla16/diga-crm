<style>

</style>

<template lang="pug">
    section.h-100.px-2
        div.chart-title {{ $t("dashboard.Regional_sales") }}
        table.text-center.h-100.w-100(v-if="widget.loading")
            tr
                td
                    div.loader.sm-loader
        template(v-if="!widget.loading")
            div.h-100(v-if="not_enough_data")
                table.text-center.h-100.w-100.no-enough-data-table
                    tr
                        td
                            div(style="font-size: 50px;")
                                i.fa.fa-database
                            div {{ $t('dashboard.Not_enough_data') }}
            div(style="padding-top:20px;")
                div
                    datatable.datatable-wrapper(v-bind="table" :per-page="5")
                        input.form-control(v-model="filters.query", type="text", :placeholder="$t('template.Search')" style="display: inline-block;flex:1;margin: 0 10px 0;")
                div
                    gmap-map(:center="google.map.position", :zoom="google.map.zoom", map-type-id="terrain", style="height: 300px", ref="map")
                        gmap-cluster
                            gmap-marker(v-for="(m, index) in google.markers" :position="m"
                                :clickable="true" :draggable="true"
                                @click="center=m"
                                :key="index")
</template>

<script>
import GmapCluster from 'vue2-google-maps/dist/components/cluster'

let geocoder;

export default {
    props: ['widget', 'offset'],
    components: {
        GmapCluster
    },
    data() {
        return {
            table: {
                columns: [
                    { title: this.$root.$t("dashboard.Region"), field: 'City', sortable: false },
                    { title: this.$root.$t("dashboard.Total_services"), field: 'Total_services', sortable: true },
                    { title: this.$root.$t("dashboard.Total_services_in_sale_status"), field: 'Total_services_in_sale_status', sortable: true },
                    { title: this.$root.$t("dashboard.Total_services_in_sale_status_percent"), field: 'Total_services_in_sale_status_percent', sortable: true },
                    { title: this.$root.$t("dashboard.Average_decision_time"), field: 'Average_decision_time', sortable: true },
                    { title: this.$root.$t("dashboard.Average_sum"), field: 'Average_sum', sortable: true },
                    { title: this.$root.$t("dashboard.Total_sum"), field: 'Total_sum', sortable: true },
                    { title: this.$root.$t("dashboard.Average_margin"), field: 'Average_margin', sortable: true },
                    { title: this.$root.$t("dashboard.Number_of_second_buys"), field: 'Number_of_second_buys', sortable: true },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
            filters: {
                query: '',
                active: 1,
            },
            google: {
                map: {
                    zoom: 13,
                    position: {lat: 38.743625, lng: -9.154922},
                },
                addresses: [
                ],
                markers: [],
                polygons: [], // geo collection here,
            },
        }
    },
    mounted(){
        
    },
    methods: {
        gecodeByAddress(address){
            let $this = this;
            if (!geocoder){
                geocoder = new google.maps.Geocoder();
            }
            
            var res = geocoder.geocode({address: address}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    $this.setMarker(results[0]);
                } else {
                    // $this.$toastr.e($this.$root.$t('client.Cannot_parse_service_address'), $this.$root.$t("template.Error"));
                }
            });
        },
        setMarker(place){
            if (!place.geometry) {
                this.$toastr.e(this.$root.$t("client.Autocomplete_returned_place_contains_no_geometry"), this.$root.$t("template.Error"));
                return;
            }

            if (!place.address_components) {
                this.$toastr.e(this.$root.$t("client.This_address_has_no_components"), this.$root.$t("template.Error"));
                return;
            }

            this.google.markers.push(place.geometry.location);
        },
        getResults(cities){
            var copy = Array.from(cities);

            copy = copy.filter((a) => {
                if (this.filters.query.length > 0){
                    return a.City.toLowerCase().includes(this.filters.query.toLowerCase());
                }
                else {
                    return true;
                }
            });
            this.table.total = copy.length;
            return copy.sort((a, b) => {
                if (this.table.query.sort){
                    if (this.table.query.order === 'desc'){
                        return b[this.table.query.sort] - a[this.table.query.sort];
                    }
                    else{
                        return a[this.table.query.sort] - b[this.table.query.sort];
                    }
                }
                else{
                    return b.Total_services - a.Total_services;
                }                
            }).slice((this.table.query.offset), (this.table.query.offset + 5));
        },
    },
    computed: {
        not_enough_data(){
            return this.widget.cities.length == 0;
        },
        use_range(){
            return this.$store.getters['stage/w_use_range'] ? this.$root.$t('dashboard.for_services_created_in_range') : '';
        },
    },
    watch: {
        'widget.cities': function(newVal){

            if (newVal.length > 0){
                this.table.data = this.getResults(newVal);
                this.table.total = newVal.length;
            }
        },
        'widget.service_addresses': function(newVal){

            if (newVal.length > 0){
                let $this = this;
                setTimeout(function(){
                    var timer = 0;
                    newVal/*.splice(0, 200)*/.forEach(element => {
                        setTimeout(function(){                        
                            $this.gecodeByAddress(element.address);                        
                        }, timer);
                        timer += 500;
                    });
                }, 4000);
            }
        },
        'table.query': {
            handler (query) {
                if (this.widget.cities && this.widget.cities.length > 0){
                    this.table.data = this.getResults(this.widget.cities);
                }                
            },
            deep: true,
        },
        'filters.active': function(){
            this.table.query.offset = 0;
            if (this.widget.cities && this.widget.cities.length > 0){
                this.table.data = this.getResults(this.widget.cities);
            } 
        },
        'filters.query': function(){
            this.table.query.offset = 0;
            if (this.widget.cities && this.widget.cities.length > 0){
                this.table.data = this.getResults(this.widget.cities);
            } 
        },
    },
}

</script>