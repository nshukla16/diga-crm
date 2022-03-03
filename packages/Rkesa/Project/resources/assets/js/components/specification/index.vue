<template lang="pug">
    div
        h2 {{ $t('template.All_specifications') }}
        section.diga-container.p-4
            datatable.datatable-wrapper.companies-wrapper(v-bind="table")
                router-link.btn.btn-diga(style="height:38px;margin: 0 10px 0;", :to="{name: 'specification_create'}") {{ $t('project.Specification_new') }}
</template>

<script>
import name_column from './custom_columns/td_name.vue';

export default {
    props: ['offset'],
    data(){
        return {
            table: {
                columns: [
                    { title: this.$root.$t('project.Name'), tdComp: name_column },
                    { title: this.$root.$t('project.Amount_of_equipment'), field: 'equipment_count' },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
        }
    },
    created() {
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.All_specifications');
    },
    methods: {
        getResults() {
            this.$http.get('/api/specifications?' + this.$root.params(this.table.query)).then(res => {
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