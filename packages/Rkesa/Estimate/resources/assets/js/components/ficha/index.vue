<style>

</style>

<template lang="pug">
    div
        h2 {{ $t('estimate.All_fichas') }}
        section.diga-container.p-4
            datatable.datatable-wrapper(v-bind="table")
                input.form-control(v-model='query', :placeholder="$t('template.Search')", type='text', style='flex: 1 1 0%;min-width: 150px;display: inline-block;margin: 0 10px 0;height:38px;')
                router-link.btn.btn-diga(v-if="$root.can_do('fichas', 'create') != 0", :to="{name: 'ficha_create'}" style="height:38px;margin: 0 10px 0;") {{ $t('estimate.New_ficha') }}
</template>

<script>
import name_column from './custom_columns/td_name'
import price_column from './custom_columns/td_price'
import actions_column from './custom_columns/td_actions'

export default {
    props: ['offset'],
    data: function() {
        return {
            query: '',
            table: {
                columns: [
                    { title: this.$root.$t('estimate.Name'), tdComp: name_column },
                    { title: this.$root.$t('estimate.Description'), field: 'description' },
                    { title: this.$root.$t('estimate.Price'), tdComp: price_column },
                    { title: this.$root.$t('template.Actions'), tdClass: 'column_autosize', tdComp: actions_column, visible: this.$root.can_do('fichas', 'delete') != 0 },
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
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('estimate.All_fichas');
        this.$bus.$on("get_results", this.getResults);
    },
    beforeDestroy: function() {
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
    methods: {
        getResults() {
            this.$http.get('/api/fichas/?' + this.$root.params(this.table.query) + (this.query != '' ? '&query=' + this.query : '')).then(res => {
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
        query: function(){
            this.table.query.offset = 0;
            this.getResults();
        },
    },
}
</script>