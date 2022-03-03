<template lang="pug">
    div
        router-link.btn.btn-secondary(v-if="$root.can_with_service('update', row)", :to="{ name: 'service_edit', params: {id: row.id }, query: {from: 'index', contact_id: row.client_contact_id}}") {{ $t('service.Service_edit') }}
        button.btn.btn-danger.ml-2(v-if="$root.can_with_service('delete', row)" v-on:click="remove_service(row.id)") {{ $t('service.Service_remove') }}
</template>

<script>
export default {
    props: ['row'],
    methods: {
        remove_service(id){
            if (confirm(this.$root.$t('client.Are_you_sure_you_want_to_delete_service'))) {
                this.$root.global_loading = true;
                this.$http.delete('/api/services/' + id).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("client.Service_removed"), this.$root.$t("template.Success"));
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