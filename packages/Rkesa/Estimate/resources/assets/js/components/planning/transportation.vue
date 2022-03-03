<template lang="pug">
div.diga-container.p-4
    h2 {{ $t('estimate.Movement_information') }}
    div
        .map-container.mb-3
            gmap-map(:center='{lat:38.743625, lng:-9.154922}', :zoom='12', style='width: 500px; height: 300px; max-width: 100%;', ref='mym')
                gmap-marker(:position='start')
                gmap-marker(:position='finish')
                template(v-for='route in routes')
                    gmap-polyline(:options='route.options', :path='route.overview_path', :editable='false', v-on:click='activate_route(route)', ref='myroute')
        button.btn.green.mr-3(v-on:click='construct_routes(route_index)') {{ $t('template.Update') }}
        a.btn.btn-diga(target='_blank', v-bind:href="'https://www.google.pt/maps/dir/R. Francisco Tomás da Costa 50, 1600-094 Lisboa/'+currentEstimate.service.address") {{ $t('estimate.Open_on_gmaps') }}
    div.form(style="padding-top: 20px;")
        div.form-group.row
            label.col-sm-4.col-form-label {{ $t('estimate.Start_Latitude') }}
            div.col-sm-8
                input.form-control(v-model='currentEstimate.estimate_details.start_point_lat')
        div.form-group.row
            label.col-sm-4.col-form-label {{ $t('estimate.Start_Langitude') }}
            div.col-sm-8
                input.form-control(v-model='currentEstimate.estimate_details.start_point_lng')
        div.form-group.row
            label.col-sm-4.col-form-label {{ $t('estimate.Distance') }} 
            div.col-sm-8
                input.form-control(disabled="disabled", :value="round10(route_distance/1000) + ' km'") 
        div.form-group.row
            label.col-sm-4.col-form-label {{ $t('estimate.Duration') }}
            div.col-sm-8
                input.form-control(disabled="disabled", :value="route_duration")
        div.form-group.row
            label.col-sm-4.col-form-label {{ $t('estimate.Fuel_consumption') }}
            div.col-sm-8
                input.form-control(v-model='currentEstimate.estimate_details.consumption_per_100_km')
        div.form-group.row
            label.col-sm-4.col-form-label {{ $t('estimate.Fuel_price') }}:
            div.col-sm-8
                input.form-control(v-model='currentEstimate.estimate_details.gasoline_price')
        div.form-group.row
            label.col-sm-4.col-form-label {{ $t('estimate.Price_per_trip') }}
            div.col-sm-8
                input.form-control(disabled="disabled", :value="road_price + ' ' + $root.current_currency.symbol")
        div.form-group.row 
            label.col-sm-4.col-form-label {{ $t('estimate.Price_for_n_days', {days_count: currentEstimate.deadline}) }}
            div.col-sm-8
                 input.form-control(disabled="disabled", :value="total_dislocation + ' ' + $root.current_currency.symbol")
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['currentEstimate'],
    data(){
        return {
            routes: [],
            route_distance: null,
            route_duration: null,
            start: null,
            finish: { lat: 0, lng: 0 },
            route_index: 0,
            days: 1,
        }
    },
    mounted(){
        let $this = this;
        this.days = this.currentEstimate.deadline;
        this.start = { lat: this.currentEstimate.estimate_details.start_point_lat, lng: this.currentEstimate.estimate_details.start_point_lng };

        setTimeout(function(){
                $this.construct_routes($this.route_index);
        }, 4000);
    },    
    methods:{
        construct_routes: function(route_index){
            this.start = { lat: parseFloat(this.currentEstimate.estimate_details.start_point_lat), lng: parseFloat(this.currentEstimate.estimate_details.start_point_lng) };
            let $this = this;
            let directionsService = new google.maps.DirectionsService();

            let geocoder = new google.maps.Geocoder();
            geocoder.geocode({ 'address': this.currentEstimate.service.address }, function(results, status) {
                if (status === 'OK') {
                    let loc = results[0].geometry.location;
                    $this.finish = { lat: loc.lat(), lng: loc.lng() };
                    $this.route_paths = [];
                    directionsService.route({
                        origin: $this.start,
                        destination: $this.finish,
                        avoidTolls: true,
                        avoidHighways: false,
                        travelMode: google.maps.TravelMode.DRIVING,
                        provideRouteAlternatives: true,
                    }, function (response, status) {
                        if (status === 'OK') {
                            $this.routes = [];
                            response.routes.forEach(function(route){
                                route.options = {
                                    strokeOpacity: 0.8,
                                    strokeWeight: 5,
                                    strokeColor: '#7a7a7a',
                                    zIndex: 1,
                                };
                                $this.routes.push(route);
                            });
                            if (route_index == null){
                                route_index = 0;
                            }
                            $this.activate_route($this.routes[route_index]);
                        } else {
                            $this.$toastr.e(status, $this.$root.$t("template.Error"));
                        }
                    });
                } else {
                    $this.$toastr.e($this.$root.$t("estimate.Address_not_found"), $this.$root.$t("template.Error"));
                }
            });
        },
        activate_route: function(route){
            this.routes.forEach(function(r){
                r.options.strokeColor = '#7a7a7a';
                r.options.zIndex = 1;
            });
            route.options.strokeColor = '#2698ee';
            route.options.zIndex = 2;
            this.route_distance = route.legs[0].distance.value;
            this.route_duration = route.legs[0].duration.text;
            this.$refs.mym.$mapObject.fitBounds(route.bounds);
            this.route_index = this.routes.indexOf(route);
        },
        round10(num){
            return Math.round(num * 100) / 100;
        },
    },
    computed:{
        road_price: function(){
            return this.round10(this.currentEstimate.estimate_details.consumption_per_100_km / 100 * this.round10(this.route_distance / 1000) * this.currentEstimate.estimate_details.gasoline_price);
        },
        total_dislocation: function(){
            let result = this.round10(this.road_price * this.days * 2);
            this.$emit('total', result);
            return result;
        },
    }
}
</script>