<template lang="pug">
    div
        h2 {{ $t('estimate.All_estimates') }}
        section.diga-container.p-4
            datatable.datatable-wrapper(v-bind="table")
                input.form-control(v-model='query', :placeholder="$t('template.Search')", type='text', style='width: 210px;display: inline-block;margin: 0 10px 0;height:38px;')
</template>

<script>
import {mapGetters} from "vuex";
import created_at_column from './custom_columns/td_created_at.vue'
import number_column from './custom_columns/td_number.vue'
import service_column from './custom_columns/td_service.vue'
import address_column from './custom_columns/td_address.vue'
import total_column from './custom_columns/td_total.vue'
import client_column from './custom_columns/td_client.vue'
import open_column from './custom_columns/td_open.vue'
import pdf_column from './custom_columns/td_pdf.vue'
import actions_column from './custom_columns/td_actions.vue'

export default {
    props: ['offset'],
    data: function() {
        return {
            query: '',
            table: {
                columns: [
                    { title: this.$root.$t('estimate.Data'), field: 'created_at', tdComp: created_at_column, sortable: true },
                    { title: '№', tdComp: number_column},
                    { title: this.$root.$t('estimate.state'), tdComp: service_column, tdClass: 'service_states' },
                    { title: this.$root.$t('estimate.Morada'), tdComp: address_column },
                    { title: this.$root.$t('estimate.Valor'), tdComp: total_column, tdStyle: 'white-space: nowrap;' },
                    { title: this.$root.$t('estimate.Client'), tdComp: client_column },
                    { title: this.$root.$t('estimate.Open'), tdComp: open_column, tdStyle: 'width: 85px;' },
                    { title: 'PDF', tdComp: pdf_column },
                    { title: this.$root.$t('template.Actions'), tdClass: 'column_autosize', tdComp: actions_column, visible: this.$root.can_do('estimates', 'delete') != 0 },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
        }
    },
    methods: {
        getResults() {
            this.$http.get('/api/estimates/?' + (this.query != '' ? 'query=' + this.query + '&' : '') + this.$root.params(this.table.query)).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.table.data = data.rows;
                    this.table.total = data.total;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            });
        },
    },
    computed: {
        ...mapGetters({
            states: 'getServiceStatesById',
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
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('estimate.All_estimates');
        this.$bus.$on("get_results", this.getResults);
    },
    beforeDestroy: function() {
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
}
</script>