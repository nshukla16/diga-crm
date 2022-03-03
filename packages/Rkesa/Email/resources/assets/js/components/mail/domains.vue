<style>
    .domains-table td{
        padding: 5px;
    }
</style>

<template lang="pug">
    div(v-if="mydomains")
        div.row.mb-3
            div.col-12
                div.diga-container.p-4
                    h2 {{ $t('email.Domains') }}
                    div.table-responsive
                        table.domains-table.w-100.mb-2
                            thead
                                tr
                                    td #
                                    td {{ $t('email.Domain') }}
                                    td {{ $t('email.Incoming_mail_server') }}
                                    td {{ $t('email.Incoming_mail_port') }}
                                    td {{ $t('email.Incoming_mail_use_ssl') }}
                                    td {{ $t('email.Outgoing_mail_server') }}
                                    td {{ $t('email.Outgoing_mail_port') }}
                                    td {{ $t('email.Outgoing_mail_use_ssl') }}
                                    td {{ $t('template.Remove') }}
                            tbody
                                tr(v-for="(domain,i) in mydomains.filter(d => !d.remove_flag)")
                                    td
                                        | {{ i+1 }}.
                                    td(:class="{ 'has-error': errors.has('domain_name_'+i) }")
                                        input.form-control(v-model="domain.domain_name" v-bind:name="'domain_name_'+i" v-bind:disabled="domain.domain_id != 0" v-validate="'required'")
                                    td(:class="{ 'has-error': errors.has('incoming_mail_server_'+i) }")
                                        input.form-control(v-model="domain.incoming_mail_server" v-bind:name="'incoming_mail_server_'+i" v-validate="'required'")
                                    td(:class="{ 'has-error': errors.has('incoming_mail_port_'+i) }")
                                        input.form-control(v-model="domain.incoming_mail_port" v-bind:name="'incoming_mail_port_'+i" v-validate="'required'")
                                    td
                                        input.form-control(type="checkbox" v-model="domain.incoming_mail_use_ssl")
                                    td(:class="{ 'has-error': errors.has('outgoing_mail_server_'+i) }")
                                        input.form-control(v-model="domain.outgoing_mail_server" v-bind:name="'outgoing_mail_server_'+i" v-validate="'required'")
                                    td(:class="{ 'has-error': errors.has('outgoing_mail_port_'+i) }")
                                        input.form-control(v-model="domain.outgoing_mail_port" v-bind:name="'outgoing_mail_port_'+i" v-validate="'required'")
                                    td
                                        input.form-control(type="checkbox" v-model="domain.outgoing_mail_use_ssl")
                                    td
                                        button.btn.red(v-on:click="remove(domain)") {{ $t('template.Remove') }}
                    button.btn(v-on:click="add()") {{ $t('template.Add') }}
        div.row
            div.col-12
                button.btn.btn-diga(v-on:click="save_settings()") {{ $t('template.Save') }}
</template>

<script>

export default {
    data() {
        return {
            mydomains: null,
        }
    },
    created(){
        this.load_domains();
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('email.Domains');
    },
    methods: {
        load_domains(){
            this.$root.global_loading = true;
            this.$http.get('/api/domains').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.mydomains = res.data;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        add(){
            let domain = {
                domain_id: 0,
                domain_name: '',
                incoming_mail_server: '',
                incoming_mail_port: '993',
                incoming_mail_use_ssl: true,
                outgoing_mail_server: '',
                outgoing_mail_port: '465',
                outgoing_mail_use_ssl: true,
                remove_flag: false,
            };
            this.mydomains.push(domain);
        },
        save_settings(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                let $this = this;
                let payload = JSON.parse(JSON.stringify(this.mydomains));
                this.$root.global_loading = true;
                this.$http.put('/api/domains', {domains: payload}).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.load_domains();
                        this.$toastr.s(this.$root.$t("template.Settings_saved"), this.$root.$t("template.Success"));
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                });
            });
        },
        remove(domain){
            if (confirm(this.$root.$t("email.Are_you_sure_want_to_delete_domain"))){
                domain.remove_flag = true;
            }
        },
    },
}

</script>