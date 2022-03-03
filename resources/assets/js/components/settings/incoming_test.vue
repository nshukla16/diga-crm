<style>

</style>

<template lang="pug">
    div.diga-container.p-4
        h2 {{ $t('client.Test') }}
        .row
            section.col-6
                h3 {{ $t('client.Perfil') }}
                fieldset.form-group(:class="{ 'has-error': errors.has('site') }")
                    | {{ $t('client.Token_from') }}
                    select.form-control(v-model="token", v-validate="'required|not_in:0'", name="site", v-bind:data-vv-as="$t('template.Site').toLowerCase()")
                        option(value="0") {{ $t('client.Not_specified') }}
                        option(v-for="site in sites" v-bind:value="site.token") {{ site.domain }}
                    span.help-block(v-show="errors.has('site')") {{ errors.first('site') }}
                fieldset.form-group
                    | {{ $t('client.Name') }}
                    input.form-control(v-model="name")
                fieldset.form-group
                    | {{ $t('client.Email') }}
                    input.form-control(v-model="email")
                fieldset.form-group
                    | {{ $t('client.Phone_number') }}
                    input.form-control(v-model="phone")
                fieldset.form-group
                    | {{ $t('client.Message') }}
                    textarea.form-control(v-model="message")
                h3 {{ $t('client.Service') }}
                fieldset.form-group
                    | {{ $t('client.Morada') }}
                    input.form-control(v-model="service_address")
                fieldset.form-group
                    | {{ $t('client.Service_type') }}
                    input.form-control(v-model="service_type")
                fieldset.form-group
                    | {{ $t('client.Note') }}
                    textarea.form-control(v-model="service_note")
            section.col-6
                h3 {{ $t('client.Extra') }}
                fieldset.form-group
                    | {{ $t('client.File') }}
                    br
                    input(type="file", @change="processFile($event)")
                fieldset.form-group
                    | {{ $t('client.Estimate_summ') }}
                    input.form-control(v-model="extra.estimate_summ")
                fieldset.form-group
                    | {{ $t('client.Paid_summ') }}
                    input.form-control(v-model="extra.paid_summ")
                button.btn.btn-diga(v-on:click="test") {{ $t('client.Test') }}
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    data: function() {
        return {
            name: '',
            email: '',
            phone: '',
            message: '',
            token: 0,
            file: null,
            service_address: '',
            service_note: '',
            service_type: '',
            extra: {
                estimate_summ: '',
                paid_summ: '',
            },
        }
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('client.Test');
    },
    methods: {
        test: function(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                var data = new FormData();
                data.append('type', "new_client");
                data.append('name', this.name);
                data.append('email', this.email);
                data.append('phone', this.phone);
                data.append('message', this.message);
                data.append('service_address', this.service_address);
                data.append('service_note', this.service_note);
                data.append('service_type', this.service_type);
                data.append('token', this.token);
                data.append('extra', JSON.stringify(this.extra));
                data.append('file', this.file);
                this.$http.post('/api', data).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("client.Request_sent"), this.$root.$t("template.Success"));
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            });
        },
        processFile(event) {
            this.file = event.target.files[0];
        },
    },
    computed: {
        ...mapGetters({
            sites: 'getSites',
        }),
    },
}

</script>