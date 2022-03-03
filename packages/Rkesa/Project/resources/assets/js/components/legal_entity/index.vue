<template lang="pug">
    div
        h2 {{ $t('project.Legal_entities') }}
        section.diga-container.p-4
            div.float-sm-right.mr-2
                router-link.btn.btn-diga(v-if="$root.can_do('legal_entities', 'create') !== 0" :to="{ name: 'legal_entity_create'}") {{ $t('template.Add') }}
            datatable.datatable-wrapper.companies-wrapper(v-bind="table")
</template>

<script>
import name_column from './custom_columns/td_name.vue';
import actions_column from './custom_columns/td_actions.vue';

export default {
    props: ['offset'],
    data(){
        return {
            filters: {
                search: '',
                responsible_user_id: 0,
            },
            table: {
                columns: [
                    { title: '#', field: 'id', sortable: true },
                    { title: this.$root.$t('project.Name'), field: 'name', tdComp: name_column, sortable: true },
                    { title: this.$root.$t('project.Actions'), tdClass: 'column_autosize', tdComp: actions_column },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
        }
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Legal_entities');
        this.$bus.$on("get_results", this.getResults);
    },
    beforeDestroy: function() {
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
    methods: {
        getResults() {
            this.$http.get('/api/legal_entities?' + this.$root.params(this.table.query) + '&' + this.$root.params(this.filters)).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.table.data = res.data.rows;
                    this.table.total = res.data.total;
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