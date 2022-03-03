<style>

</style>

<template lang="pug">
    div
        h2 {{ $t('template.All_services')}}
        event_popup(:current_event="current_event", :current_contact="current_contact")
        section.diga-container.p-4
            datatable.datatable-wrapper(v-bind="table")
                input.form-control(v-model='query', :placeholder="$t('template.Search')", type='text', style='flex: 1 1 0%;min-width: 150px;display: inline-block;margin: 0 10px 0;height:38px;')
                select.form-control(v-model="filters.service_state_id" style="display: inline-block;min-width: 150px;flex:1;margin: 0 10px 0;")
                    option(value="0") {{ $t('client.All') }}
                    option(v-for="service_state in service_states", :value="service_state.id") {{ service_state.name }}
                select.form-control(v-if="$root.can_do('services', 'read') > 1" v-model="filters.responsible_user_id" style="display: inline-block;min-width: 150px;flex:1;margin: 0 10px 0;")
                    option(value="0") {{ $t('client.All') }}
                    option(v-for="user in users", :value="user.id") {{ user.name }}
                button.btn.btn-diga(v-if="$root.user.can_export === true" @click="export_results" style="height: 38px;margin: 0 10px 0;") {{ $t('template.Export') }}
</template>

<script>
import moment from 'moment';
import { mapGetters } from 'vuex';

import created_at_column from './custom_columns/td_created_at.vue';
import service_number_column from './custom_columns/td_service_number.vue';
import service_state_column from './custom_columns/td_service_state.vue';
import phone_column from './custom_columns/td_phone.vue';
import company_column from './custom_columns/td_company.vue';
import responsible_column from './custom_columns/td_responsible.vue';
import contact_column from './custom_columns/td_contact.vue';
import actions_column from './custom_columns/td_actions.vue';
import tasks_column from './custom_columns/td_tasks.vue';

import event_popup from './../../../../../../Calendar/resources/assets/js/components/calendar/event_popup.vue';

export default {
    props: ['offset', 'responsible_user_id', 'q'],
    components: {
        'event_popup': event_popup,
        // tds
        'created_at_column': created_at_column,
        'service_number_column': service_number_column,
        'service_state_column': service_state_column,
        'phone_column': phone_column,
        'company_column': company_column,
        'responsible_column': responsible_column,
        'contact_column': contact_column,
        'actions_column': actions_column,
        'tasks_column': tasks_column,
    },
    data: function() {
        return {
            query: this.q || '',
            table: {
                supportBackup: true,
                columns: [
                    { title: this.$root.$t('service.Service_date'), field: 'created_at', tdComp: 'created_at_column', sortable: true },
                    { title: '№', field: 'name', tdComp: 'service_number_column', tdStyle: 'width: 100px;' },
                    { title: this.$root.$t('service.Service_name'), field: 'name', sortable: true },
                    { title: this.$root.$t('client.state'), tdComp: 'service_state_column', tdClass: 'service_states' },
                    { title: this.$root.$t('service.SMS_phone'), tdComp: 'phone_column' },
                    { title: this.$root.$t('service.Company_name'), tdComp: 'company_column' },
                    { title: this.$root.$t('service.Responsible'), tdComp: 'responsible_column' },
                    { title: this.$root.$t('service.Service_client'), tdComp: 'contact_column' },
                    { title: this.$root.$t('service.Tasks'), tdComp: 'tasks_column', visible: false },
                    { title: this.$root.$t('template.Actions'), tdClass: 'column_autosize', tdComp: 'actions_column', visible: this.$root.can_do('services', 'update') != 0 },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
            filters: {
                responsible_user_id: this.responsible_user_id || 0,
                service_state_id: 0,
            },
            // timer_id: null,
            // current_url: ''
            current_event: null,
            current_contact: null,
        }
    },
    methods: {
        filter_string(){
            return (this.query != '' ? 'query=' + this.query + '&' : '') + 'service_state_id=' + this.filters.service_state_id + '&responsible_user_id=' + this.filters.responsible_user_id + '&' + this.$root.params(this.table.query);
        },
        getResults() {
            // if(this.timer_id != null){
            //     clearTimeout(this.timer_id);
            //     this.timer_id = null;
            // }
            this.$http.get('/api/services/?' + this.filter_string()).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.table.data = data.rows;
                    this.table.total = data.total;

                    // this.timer_id = setTimeout(this.save_url, 600);
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            });
        },
        // save_url(){
        // window.history.pushState("string", null, this.current_url);
        // },
        export_results(){
            this.$root.global_loading = true;
            this.$http.get('/api/services/export?' + this.filter_string(), {responseType: 'blob'}).then(response => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'services-' + moment().format("DD-MM-YYYY-HH-mm-ss") + '.xlsx');
                document.body.appendChild(link);
                link.click();
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        open_event_modal(event){
            this.current_event = event;
            this.current_contact = this.current_event.client_contact;
            jQuery('#calendarModal').modal();
        },
    },
    computed: {
        ...mapGetters({
            users: 'getNotRemovedUsers',
            service_states: 'getNotRemovedServiceStates',
        }),
    },
    watch: {
        'table.query': {
            handler (query) {
                this.getResults();
            },
            deep: true,
        },
        query: function(){
            this.table.query.offset = 0;
            this.getResults();
        },
        'filters.responsible_user_id': function(){
            this.table.query.offset = 0;
            this.getResults();
        },
        'filters.service_state_id': function(){
            this.table.query.offset = 0;
            this.getResults();
        },
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('service.All_Services');
        this.$bus.$on("get_results", this.getResults);
        this.$bus.$on("services_open_event", this.open_event_modal);
    },
    beforeDestroy: function() {
        this.getResults && this.$bus.$off("get_results", this.getResults);
        this.open_event_modal && this.$bus.$off("services_open_event", this.open_event_modal);
    },
}
</script>