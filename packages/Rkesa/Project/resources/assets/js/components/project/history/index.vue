<template lang="pug">
div
    h2 {{ $t('project.Project_history') }}
    section.diga-container.p-4
        div.float-sm-right.mr-2
            router-link.btn.btn-diga(style="height:38px;", :to="{name: 'project_show', params: {id: this.id}}") {{ $t('project.Go_to_the_project') }}
        datatable.datatable-wrapper.companies-wrapper(v-bind="table")
</template>

<script>
import author_column from './custom_columns/td_author.vue';
import entity_column from './custom_columns/td_entity.vue';
import old_values_column from './custom_columns/td_old_values.vue';
import new_values_column from './custom_columns/td_new_values.vue';
import created_at_column from './custom_columns/td_created_at.vue';
import event_column from './custom_columns/td_event.vue';
import {mapGetters} from "vuex";

export default {
    props: ['offset', 'id'],
    data(){
        return {
            filters: {
                search: '',
            },
            table: {
                columns: [
                    { title: '#', field: 'id', sortable: true },
                    { title: this.$root.$t('template.History_author'), tdComp: author_column, field: 'user_id', sortable: true },
                    { title: this.$root.$t('template.History_event'), field: 'event', tdComp: event_column, sortable: true },
                    { title: this.$root.$t('template.History_entity'), tdComp: entity_column, field: 'auditable_type', sortable: true },
                    { title: this.$root.$t('template.History_old_values'), tdComp: old_values_column, field: 'old_values', sortable: false },
                    { title: this.$root.$t('template.History_new_values'), tdComp: new_values_column, field: 'new_values', sortable: false },
                    { title: this.$root.$t('template.History_date'), tdComp: created_at_column, field: 'created_at', sortable: true },
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
        }),
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Project_history');
        this.$bus.$on("get_results", this.getResults);
    },
    methods: {
        getResults() {
            this.$http.get('/api/projects/audit/' + this.id + '?' + this.$root.params(this.table.query) + '&' + this.$root.params(this.filters)).then(res => {
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
