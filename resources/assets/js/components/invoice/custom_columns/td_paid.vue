<template lang="pug">
    div
        button.btn.btn-diga(v-on:click="pay(row.id)")
            template(v-if="row.is_paid === true") {{ $t('template.Yes') }}
            template(v-else) {{ $t('template.No') }}
</template>

<script>
export default {
    props: ['row'],
    methods: {
        pay(id) {
            if (this.$root.can_do('invoices', 'update') === 0){
                this.$toastr.w(this.$root.$t("template.not_enough_rights"), this.$root.$t("template.Warning"));
                return;
            }
            if (confirm(this.$root.$t('calendar.AreYouSure'))) {
                this.$http.post('/api/invoices/pay/' + id, {}).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
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