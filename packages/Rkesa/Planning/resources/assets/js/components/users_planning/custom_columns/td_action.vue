<template lang="pug">
    div
        button.btn.btn-danger(v-if="this.$root.user.is_admin" v-on:click="remove_plan(row.id)") {{ $t('template.Remove') }}
</template>

<script>
export default {
    props: ['row'],
    methods: {
        remove_plan(id) {
            if (confirm(this.$root.$t('gantt.User_task_delete'))){
                this.$http.delete('/api/user_plannings/' + id).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("estimate.Roadmap_removed"), this.$root.$t("template.Success"));
                        this.$bus.$emit('get_results');
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            }
        },
    },
}
</script>