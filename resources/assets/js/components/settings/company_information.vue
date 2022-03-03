
<template lang="pug">
    div
        div.row
            div.col-6.col-md-6.mb-3
                div.diga-container.p-4(v-if="settings")
                    h2 {{ $t('template.Company_information') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Company_name') }}
                                input.form-control(v-model="settings.name")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Address') }}
                                input.form-control(v-model="settings.address")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Postcode') }}
                                input.form-control(v-model="settings.postal_code")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.City') }}
                                input.form-control(v-model="settings.city")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Mailchimp_phone') }}
                                input.form-control(v-model="settings.phone")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Company_fax') }}
                                input.form-control(v-model="settings.fax")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label Email
                                input.form-control(v-model="settings.email")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{$t('template.Company_web_site')}}
                                input.form-control(v-model="settings.web_site")
            div.col-6.col-md-6.mb-3
                div.diga-container.p-4(v-if="settings")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{$t('template.crc')}}
                                input.form-control(v-model="settings.crc")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{$t('template.crc_number')}}
                                input.form-control(v-model="settings.crc_number")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{$t('template.Company_capital')}}
                                input.form-control(v-model="settings.capital")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{$t('template.Company_tax_number')}}
                                input.form-control(v-model="settings.tax_number")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{$t('template.Company_bank')}}
                                input.form-control(v-model="settings.bank")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label IBAN
                                input.form-control(v-model="settings.iban")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label SWIFT
                                input.form-control(v-model="settings.swift")
        div.row
            div.col-12.d-flex.justify-content-between
                button.btn.btn-diga(v-on:click="save_settings()") {{ $t('template.Save') }}
</template>

<script>
export default {
    data() {
        return {
            settings: null,
        }
    },
    created(){
        this.get_settings();
    },
    methods: {
        get_settings(){
            this.$http.get('/api/company_information').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.settings = res.data;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });            
        },
        save_settings(){
            this.$http.post('/api/company_information', this.settings).then(res => {
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
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.Company_information');
    },
}
</script>