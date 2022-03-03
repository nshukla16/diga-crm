<style>
    .notifications-table thead{
        display: none;
    }
    .notifications-table td{
        padding: 0;
    }
</style>

<template lang="pug">
    div
        h2 {{ $t('template.Notifications') }}
        section.diga-container.p-4
            div.d-flex.justify-content-between.flex-wrap(style="margin: 0 -10px;")
                div.not-wrapper
                    datatable.datatable-wrapper.notifications-table(v-if="table.data.length != 0" v-bind="table")
                        a(style="padding-left: 20px;", href="#" v-text="$t('template.Mark_all_read')", v-on:click="mark_all_as_read()")
                    div(v-else style="padding: 10px;text-align: center;" v-text="$t('template.Here_will_be_notifications')")
</template>

<script>
import not_column from './custom_columns/td_not.vue';

export default {
    data: function() {
        return {
            table: {
                columns: [
                    { title: '#', tdComp: not_column },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
        }
    },
    props: ['offset'],
    methods: {
        getResults() {
            this.$http.get('/api/notifications?' + this.$root.params(this.table.query)).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.table.total = data.total;
                    this.table.data = data.rows;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
        notif_read(not_id){
            let notif = this.table.data.find(x => x.id === not_id);
            if (notif) {
                notif.read = !notif.read;
            }
        },
        notif_page_add_not(){
            this.getResults();
        },
        mark_all_as_read(){
            this.$root.global_loading = true;
            this.table.data.forEach(notif => {
                this.$http.post('/api/notifications/read_all', {id: notif.id}).then(res => {
                    this.$root.global_loading = false;
                    if (res.data.errcode == 1) {
                        $this.$toastr.e(res.data.errmess, $this.$t("template.Error"));
                    } else {
                        location.reload();
                    }
                }, res => {
                    this.$root.global_loading = false;
                    $this.$toastr.e(res.data.errmess, $this.$t("template.Error"));
                });
            });
        }
    },
    watch: {
        'table.query': {
            handler (query) {
                this.getResults();
            },
            deep: true,
        },
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.Notifications');
        this.$bus.$on("notif_read", this.notif_read);
        this.$bus.$on("notif_page_add_not", this.notif_page_add_not);
        this.getResults();
    },
    beforeDestroy: function() {
        this.notif_read && this.$bus.$off("notif_read", this.notif_read);
        this.notif_page_add_not && this.$bus.$off("notif_page_add_not", this.notif_page_add_not);
    },
}
</script>