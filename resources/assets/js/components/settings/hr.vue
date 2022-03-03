
<template lang="pug">
    div
        div.row
            div.col-12.col-md-12.mb-3
                div.diga-container.p-4(v-if="global_settings")
                    h2 {{ $t('template.hr_settings') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.vacations_days_per_year') }}
                                input.form-control(type="number"  min="0", step="1" v-model="global_settings.invoice_auto_send_email_password")

        div.row
            div.col-12.d-flex.justify-content-between
                button.btn.btn-diga(v-on:click="save_settings()") {{ $t('template.Save') }}
</template>

<script>
export default {
    data() {
        return {
            global_settings: null,
        }
    },
    created(){
    },
    methods: {
        save_settings(){
            this.$root.global_loading = true;
            let payload = Object.assign({}, this.global_settings);

            this.$http.post('/api/global_settings', payload).then(res => {               
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.$toastr.s(this.$root.$t("template.Settings_saved"), this.$root.$t("template.Success"));
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.hr_settings');
        this.global_settings = this.$store.getters.getGlobalSettings;
    },
}
</script>