<template lang="pug">
div
    h2 {{ $t('project.Projects') }}
    section.diga-container.p-4
        div.float-sm-right.mr-2(v-if="$root.can_do('projects', 'create') == 1")
            router-link.btn.btn-diga(style="height:38px;", :to="{name: 'project_create'}") {{ $t('project.Project_new') }}
        datatable.datatable-wrapper.companies-wrapper(v-bind="table")
            input.form-control(v-model="filters.search", type="text", :placeholder="$t('client.Search')", style="flex:1;margin: 0 10px 0 10px;")
            select.form-control(style="flex: 1;margin: 0 10px 0 0;" v-model="filters.responsible_user_id")
                option(:value="0") {{ $t('template.Choose') }}
                option(v-for="user in users", :value="user.id") {{ user.name }}
            select.form-control(style="flex: 1;margin: 0 10px 0 0;" v-model="filters.project_status_id")
                option(:value="0") {{ $t('template.Choose') }}
                option(v-for="ps in project_statuses", :value="ps.id") {{ ps.name }}

</template>

<script>
import project_name_column from './custom_columns/td_project_name.vue';
import project_status_column from './custom_columns/td_status.vue';
import project_type_column from './custom_columns/td_project_type.vue';
import contract_type_column from './custom_columns/td_contract_type.vue';
import responsible_column from './custom_columns/td_responsible.vue';
import actions_column from './custom_columns/td_actions.vue';
import shipping_orders from './custom_columns/td_shipping_orders.vue';
import project_history_column from './custom_columns/td_project_history.vue';
import finished_at_column from './custom_columns/td_project_finished_at.vue';
import client_column from './custom_columns/td_client.vue';
import contract_price_column from './custom_columns/td_contract_price.vue';
import {mapGetters} from "vuex";

export default {
    props: ['offset'],
    data(){
        return {
            filters: {
                search: '',
                responsible_user_id: 0,
                project_status_id: 0,
            },
            table: {
                columns: [
                    { title: '#', field: 'id', sortable: true },
                    { title: this.$root.$t('project.Name'), field: 'name', tdComp: project_name_column, sortable: true },
                    { title: this.$root.$t('project.Project_status'), tdComp: project_status_column, field: 'project_status_id', sortable: true },
                    { title: this.$root.$t('project.Finished_at'), tdComp: finished_at_column, field: 'finished_at', sortable: true },
                    { title: this.$root.$t('project.Project_type'), field: 'project_type_id', tdComp: project_type_column, sortable: true },
                    { title: '№ ' + this.$root.$t("project.Of_contract"), field: 'contract_number', sortable: true},
                    { title: this.$root.$t('project.Type_of_contract'), field: 'contract_type', tdComp: contract_type_column, sortable: true },
                    { title: this.$root.$t('project.Price_of_the_contract'), tdComp: contract_price_column, field: 'contract_price', sortable: true},
                    { title: this.$root.$t('project.Manufacturer_orders'), field: 'orders', tdComp: shipping_orders, sortable: true },
                    { title: this.$root.$t('project.Responsible'), tdComp: responsible_column, field: 'responsible_user_id', sortable: true },
                    { title: this.$root.$t('project.Project_history'), tdComp: project_history_column, field: 'id'},
                    { title: this.$root.$t('project.Limit_for_shipment'), field: 'delivery_terms', visible: false},
                    { title: this.$root.$t('project.Buyer'), tdComp: client_column, field: 'client_id'},
                    // { title: this.$root.$t('project.Total'), field: 'total', sortable: true },
                    { title: this.$root.$t('project.Actions'), tdClass: 'column_autosize', tdComp: actions_column, visible: this.$root.can_do('projects', 'delete') != 0 },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
        }
    },
    computed: {
        ...mapGetters({
            users: 'getNotRemovedUsers',
            project_statuses: 'getProjectStatuses',
        }),
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Projects');
        this.$bus.$on("get_results", this.getResults);
    },
    beforeDestroy: function() {
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
    methods: {
        proj_roles(){
            return this.user.roles.find(r => r.action == 'projects');
        },
        getResults() {
            this.$root.global_loading = true;
            this.$http.get('/api/projects?' + this.$root.params(this.table.query) + '&' + this.$root.params(this.filters)).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.table.data = data.rows;
                    this.table.total = data.total;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$root.global_loading = false;
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
        filters: {
            handler(query) {
                this.table.query.offset = 0;
                this.getResults();
            },
            deep: true,
        },
    },
}
</script>