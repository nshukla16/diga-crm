<template lang="pug">
    section.diga-container.p-4
        datatable.datatable-wrapper(v-bind="table")
            router-link.btn.btn-diga(:to="{name: 'estimate_fork_create'}" style="height:38px;margin: 0 10px 0;") {{ $t('estimate_fork.New_estimate_fork') }}
</template>

<script>
import name_column from './custom_columns/td_name.vue';

export default {
    props: ['offset'],
    data: function() {
        return {
            table: {
                columns: [
                    { title: this.$root.$t('estimate_fork.Name'), tdComp: name_column },
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
        getResults(page) {
            if (typeof page === 'undefined') {
                page = 1;
            }
            this.$http.get('/api/estimate_forks/?fields=id,name&page=' + page).then(res => {
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
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('estimate_fork.Forks');
    },
}
</script>