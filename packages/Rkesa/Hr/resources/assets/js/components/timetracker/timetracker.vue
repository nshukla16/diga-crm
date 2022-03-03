<style>
    #timetracker-employee-area {
        margin: 0 auto;
        width: 200px;
        text-align: center;
    }
    #timetracker-employee-area select {
        margin-bottom: 20px;
    }
</style>

<template lang="pug">
    section#timetracker-employee-area
        fieldset.form-group
            label {{ $t('client.Contact') }}
            div.input-group
                v-select(style="width: 100%;",
                    :debounce='250',
                    :on-search='get_client_contact_options',
                    :on-change='client_contact_select',
                    :options='client_contacts',
                    :placeholder="$t('client.Contact')")
                    template(slot="no-options") {{ $t('template.No_matching_options') }}
        fieldset.form-group
            label {{ $t('client.Service') }}
            div.input-group
                v-select(style="width: 100%;",
                    :debounce='250',
                    :on-search='get_services_options',
                    :on-change='services_select',
                    :options='services',
                    :placeholder="$t('client.Service')")
                    template(slot="no-options") {{ $t('template.No_matching_options') }}
        fieldset.form-group
            label {{ $t('gantt.Estimate') }}
            v-select.mb-3(
                :debounce='250',
                :on-search='get_estimates_options',
                :on-change='estimate_select',
                :options='estimates',
                :placeholder="$t('gantt.Choose_estimate')")
                template(slot="no-options") {{ $t('template.No_matching_options') }}

        //- div(v-show="this.checkpointState == 0")
        //-     | {{ $t("hr.Select_estimate") }}
        //-     select.form-control(v-model="filters.estimate")
        //-         option(value="0") {{ $t("hr.Without_estimate") }}
        //-         option(v-for="estimate_worker in estimates", v-bind:value="estimate_worker.estimate.id") {{ $root.estimate_number(estimate_worker.estimate) }}
        button.btn.btn-diga(v-text="getCheckPointText()", v-on:click="newCheckpoint()")
</template>

<script>
export default {
    data() {
        return {
            filters: {
                estimate: 0,
            },
            totals: '0:0:0',
            checkpointState: 0,
            estimates: [],
            services: [],
            client_contacts: [],
            client_contact_id: null,
            service_id: null, 
            estimate_id: null
        }
    },
    methods: {
        getCheckPointText() {
            return [this.$root.$t('hr.Start_work'), this.$root.$t('hr.Stop_work')][this.checkpointState];
        },
        // getEstimates() {
        //     this.$http.get('/api/timetracker/personal').then(res => {
        //         if (res.ok) {
        //             this.estimates = res.body;
        //         } else {
        //             this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
        //         }
        //     }, res => {
        //         this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
        //     });
        // },
        get_client_contact_options(search, loading) {
            loading(true);
            this.$http.get('/api/contacts?query=' + search).then(res => {
                var processedData = [];
                let $this = this;
                res.data.rows.forEach(function(i){
                    processedData.push({'label': $this.$root.fullName(i), 'value': i.id});
                });                
                this.client_contacts = processedData;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            })
        },
        client_contact_select(res){
            if(res === null){
                this.client_contact_id = null;
            }
            if (typeof res === 'object' && res !== null) {
                this.client_contact_id = res.value;
            }
        },
        get_services_options(search, loading) {
            loading(true);
            var url = '/api/services?query=' + search;
            if (this.client_contact_id > 0){
                url += '&client_contact_id=' + this.client_contact_id;
            }
            this.$http.get(url).then(res => {
                var processedData = [];
                let $this = this;
                res.data.rows.forEach(function(i){
                    processedData.push({'label': (i.name === null ? '' : i.name.substr(0, 60) + '... ')  + $this.$root.service_number(i), 'value': i.id});
                });
                this.services = processedData;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            })
        },
        services_select(res){
            if(res === null){
                this.service_id = null;
            }
            if (typeof res === 'object' && res !== null) {
                this.service_id = res.value;
            }
        },
        get_estimates_options(search, loading) {
            loading(true);
            this.$http.get('/api/estimates?limit=20&query=' + search).then(res => {
                var processedData = [];
                let $this = this;
                res.data.rows.forEach(function(i){
                    processedData.push({'label': $this.$root.estimate_number(i), 'value': i.id});
                });
                this.estimates = processedData;
                loading(false);
            }, res => {
                this.$toastr.e(this.$t("template.Something_bad_happened"), this.$t("template.Error"));
            })
        },
        estimate_select(res){
            if(res === null){
                this.estimate_id = null;
            }
            if (typeof res === 'object' && res !== null) {
                this.estimate_id = res.value;
            }
        },
        getCurrentData() {
            this.$http.post('/api/timetracker/employee', { estimate: this.filters.estimate }).then(res => {
                if (res.ok) {
                    this.checkpointState = res.body.state;
                    this.totals = res.body.total;
                } else {
                    this.$toastr.e(this.$root.$t('hr.Cant_retrieve_daily_data'), this.$root.$t("template.Error"));
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            });
        },
        newCheckpoint() {
            // if(this.checkpointState == 0 && this.filters.estimate == 0) {
            //     this.$toastr.e(this.$root.$t("hr.Estimate_not_set"), this.$root.$t("template.Error"));
            // }else {
            this.$root.global_loading = true;
            if ("geolocation" in navigator) {
                let $this = this;
                navigator.geolocation.getCurrentPosition(function(position) {
                    $this.$http.post('/api/timetracker/checkpoint', {estimate: $this.filters.estimate, lat: position.coords.latitude, lng: position.coords.longitude}).then(res => {
                        if (res.data.errcode == 0){
                            $this.getCurrentData();
                        } else {
                            $this.$toastr.e(res.data.errmess, $this.$root.$t("template.Error"));
                        }
                        $this.$root.global_loading = false;
                    }, res => {
                        $this.$toastr.e($this.$root.$t("template.Something_bad_happened"), $this.$root.$t("template.Error"));
                        $this.$root.global_loading = false;
                    });
                }, function(err){
                    $this.$toastr.e($this.$root.$t("hr.Failed_to_get_geolocation") + ":" + err.message, $this.$root.$t("template.Error"));
                    $this.$root.global_loading = false;
                }, {timeout: 5000, enableHighAccuracy: true, maximumAge: Infinity});
            } else {
                this.$toastr.e(this.$root.$t("hr.Geolocation_unavailable"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            }
            //                }
        },
    },
    mounted(){
        // this.getEstimates();
        this.getCurrentData();
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('hr.Timetracker');
    },
}
</script>