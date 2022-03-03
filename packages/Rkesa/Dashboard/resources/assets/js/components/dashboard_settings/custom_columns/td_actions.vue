<template lang="pug">
    button.btn.btn-danger(v-on:click="delete_dashboard") {{ $t('template.Remove') }}
</template>

<script>
export default {
    props: ['row'],
    methods: {
        delete_dashboard(){
            if (confirm(this.$root.$t('dashboard.Are_you_sure_you_want_to_delete_dashboard'))) {
                this.$http.delete('/api/dashboards/' + this.row.id).then(data => {
                    if (data.errcode == 1) {
                        this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("hr.User_removed"), this.$root.$t("template.Success"));
                        this.$bus.$emit('get_results');
                        this.$store.dispatch('dashboardsRequest');
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            }
        },
    },
}
</script>