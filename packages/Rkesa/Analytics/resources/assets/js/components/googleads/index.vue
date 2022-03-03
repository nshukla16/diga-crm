<template lang="pug">
    div               
        h2 Google Ads
        section.diga-container.p-4
            datatable.datatable-wrapper.companies-wrapper(v-bind="table")   
                    
</template>

<script>

export default {
    props: ['offset'],
    data(){
        return {
           
            table: {
                columns: [
                    { title: '#', field: 'id', sortable: false },
                    { title: this.$root.$t('template.Name'), field: 'name', sortable: false},
                    { title: this.$root.$t('client.Date'), field: 'status', sortable: false },
                    { title: this.$root.$t('template.ServingStatus'), field: 'servingStatus', sortable: false },
                    { title: this.$root.$t('template.StartDate'), field: 'StartDate', sortable: false },
                    // { title: this.$root.$t('estimate.Documents'), tdComp: td_children, sortable: false },
                    // { title: this.$root.$t('service.Company_name'), field: 'client_id', tdComp: td_client, sortable: true },
                    // { title: this.$root.$t('calendar.Contact'), field: 'client_contact_id', tdComp: td_client_contact, sortable: true },
                    // { title: this.$root.$t('calendar.Service'), field: 'service_id', tdComp: td_service, sortable: true },
                    // { title: this.$root.$t('client.Estimate'), field: 'estimate_id', tdComp: td_estimate, sortable: true },
                    // { title: this.$root.$t('template.pay_stage'), field: 'pay_stage_id', tdComp: td_pay_stage, sortable: true },
                    // { title: this.$root.$t('template.payment_event_paid'), field: 'is_paid', tdComp: td_paid, sortable: true },
                    // { tdComp: td_edit, sortable: false },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
            filters: {                
            },
        }
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | Google ads';
        this.$bus.$on('get_results', this.getResults);
    },
    beforeDestroy(){
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
    methods: {
        getResults() {
            let url = '/api/google_ads/campaigns?';

            this.$http.get(url +               
                this.$root.params(this.table.query)).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.table.data = data.rows;
                    this.table.total = data.total;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
    },
    watch: {
        'table.query': {
            handler(query) {
                this.getResults();
            },
            deep: true,
        },
    },   
}
</script>