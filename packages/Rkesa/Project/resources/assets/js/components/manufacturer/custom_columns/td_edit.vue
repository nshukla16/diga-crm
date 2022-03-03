<template lang="pug">
    div
        router-link.btn.btn-secondary(style="margin-right: 5px", :to="{name: 'manufacturer_edit', params: {id: row.id}}") {{ $t('service.Service_edit') }}
        button.btn.btn-danger(v-on:click="remove_manufacturer(row.id)") {{ $t('client.Delete') }}
</template>

<script>
export default {
    props: ['row'],
    methods: {
        remove_manufacturer(id) {
            if (confirm(this.$root.$t('project.Are_you_sure_you_want_to_delete_manufacturer'))) {
                // let url = this.$root.contact_or_client_show(id);
                this.$http.delete('/api/manufacturers/' + id).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("client.Contact_removed"), this.$root.$t("template.Success"));
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