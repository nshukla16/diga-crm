 <template lang="pug">
    div
        h2 {{ $t('template.My_works') }}
        section.diga-container.p-4
            datatable.datatable-wrapper.companies-wrapper(v-bind="table")                
                v-select(style="width: 200px;",
                    :debounce='250',
                    :on-search='get_base_options',
                    :on-change='base_select',
                    :options='bases',
                    :placeholder="$t('estimate.Choose_estimate')")

</template>

<script>
import edit_button from './custom_columns/td_edit.vue';
import number_column from './custom_columns/td_number.vue';
import service_column from './custom_columns/td_service.vue';
import address_column from './custom_columns/td_address.vue';
import client_column from './custom_columns/td_client.vue';

export default {
    props: ['offset'],
    data(){
        return {
            bases: [],
            table: {
                columns: [
                    { title: '№', tdComp: number_column},
                    { title: this.$root.$t('estimate.state'), tdComp: service_column, tdClass: 'service_states' },
                    { title: this.$root.$t('estimate.Morada'), tdComp: address_column },
                    { title: this.$root.$t('estimate.Client'), tdComp: client_column },
                    { title: this.$root.$t('estimate.Actions'), tdClass: 'column_autosize', tdComp: edit_button, sortable: false },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
            filters: {
                estimate_id: this.estimate_id || 0,
            },
        }
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.My_works');
        this.$bus.$on('get_results', this.getResults);
        this.filters.estimate_id = this.estimate_id;
    },
    beforeDestroy(){
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
    methods: {
        getResults() {
            this.$http.get('/api/estimate_groups?' + 'group_id=' + this.$root.user.group_id + '&estimate_id=' + this.filters.estimate_id + '&' + this.$root.params(this.table.query)).then(res => {
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
        get_base_options(search, loading) {
            loading(true);
            this.$http.get('/api/user_plannings?search=' + search).then(res => {
                var processedData = [];
                let $this = this;
                res.data.forEach(function(i){
                    processedData.push({'label': $this.$root.estimate_number(i), 'value': i.id});
                });
                this.bases = processedData;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            })
        },
        base_select(res){
            if(res === null){
                this.filters.estimate_id = 0;
            }
            if (typeof res === 'object' && res !== null) {
                this.filters.estimate_id = res.value;
            }
        },
    },
    watch: {
        'table.query': {
            handler(query) {
                this.getResults();
            },
            deep: true,
        },
        'filters.estimate_id': function(){
            this.table.query.offset = 0;
            this.getResults();
        },
    },
}
</script>