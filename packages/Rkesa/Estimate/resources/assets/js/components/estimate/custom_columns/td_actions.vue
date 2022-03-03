<template lang="pug">
    button.btn.btn-danger(v-if="row.can_be_deleted" v-on:click="delete_estimate(row.id)") {{ $t('template.Remove') }}
</template>

<script>
export default {
    props: ['row'],
    methods: {
        delete_estimate(id){
            if (confirm(this.$root.$t('estimate.Are_you_sure_you_want_to_delete_estimate'))) {
                this.$http.delete('/api/estimates/' + id).then(data => {
                    if (data.errcode == 1) {
                        this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("estimate.Estimate_removed"), this.$root.$t("template.Success"));
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