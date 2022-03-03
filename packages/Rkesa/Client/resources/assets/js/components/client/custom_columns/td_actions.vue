<template lang="pug">
    button.btn.btn-danger(v-if="row.can_be_deleted" v-on:click="delete_client(row.id)") {{ $t('template.Remove') }}
</template>

<script>
export default {
    props: ['row'],
    methods: {
        delete_client(id){
            if (confirm(this.$root.$t('client.Are_you_sure_you_want_to_delete_client'))) {
                this.$root.global_loading = true;
                this.$http.delete('/api/companies/' + id).then(data => {
                    if (data.errcode == 1) {
                        this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("client.Company_removed"), this.$root.$t("template.Success"));
                        this.$bus.$emit('get_results');
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                });
            }
        },
    },
}
</script>