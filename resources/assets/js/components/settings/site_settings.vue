<style>
    .sites-table td{
        padding: 5px;
    }
</style>

<template lang="pug">
    div
        div.row
            div.col-12.col-md-6.mb-3
                div.diga-container.p-4
                        h2 {{ $t('template.Incoming_requests_settings') }}
                        fieldset.form-group
                            label.control-label.col-xs-4 {{ $t('template.New_client_service_state') }}
                            div.col-xs-8
                                select.form-control(v-model="global_settings.new_service_state_id")
                                    option(v-for="state in states" v-bind:value="state.id" v-if="state.type == 0") {{ state.name }}
                        fieldset.form-group
                            label.control-label.col-xs-4 {{ $t('template.New_client_service_responsible_user') }}
                            div.col-xs-8
                                select.form-control(v-model="global_settings.responsible_user_id")
                                    option(v-for="user in users" v-bind:value="user.id") {{ user.name }}
                        fieldset.form-group
                            label.control-label.col-xs-4 {{ $t('template.New_client_task_type') }}
                            div.col-xs-8
                                select.form-control(v-model="global_settings.new_event_type_id")
                                    option(v-for="event_type in event_types" v-bind:value="event_type.id") {{ event_type.title }}
                        fieldset.form-group
                            label.control-label.col-xs-4 {{ $t('template.New_client_send_sms') }}
                            div.col-xs-8
                                input(type="checkbox" v-model="global_settings.incoming_sms")
                        fieldset.form-group(v-if="global_settings.incoming_sms")
                            label.control-label.col-xs-4 {{ $t('template.New_client_sms_text') }}
                            div.col-xs-8
                                textarea.form-control(v-model="global_settings.incoming_sms_text")
                        fieldset.form-group
                            label.control-label.col-xs-4 {{ $t('template.New_client_send_mail') }}
                            div.col-xs-8
                                input(type="checkbox" v-model="global_settings.incoming_mail")
                        template(v-if="global_settings.incoming_mail")
                            fieldset.form-group
                                label.control-label.col-xs-4 {{ $t('template.New_client_mail_address') }}
                                div.col-xs-8
                                    input.form-control(v-model="global_settings.incoming_mail_address")
                            fieldset.form-group
                                label.control-label.col-xs-4 {{ $t('template.New_client_mail_subject') }}
                                div.col-xs-8
                                    input.form-control(v-model="global_settings.incoming_mail_subject")
                            fieldset.form-group
                                label.control-label.col-xs-4 {{ $t('template.New_client_mail_text') }}
                                div.col-xs-8
                                    textarea.form-control(v-model="global_settings.incoming_mail_text")
                        router-link(:to="{name: 'client_incoming_test'}") {{ $t('client.Test') }}
            div.col-12.col-md-6.mb-3
                div.diga-container.p-4
                    h2 {{ $t('template.Sites') }}
                    table.sites-table.mb-2(style="width:100%")
                        tr
                            td #
                            td {{ $t('template.Domain') }}
                            td {{ $t('template.Token') }}
                            td {{ $t('template.Actions') }}
                        tr(v-for="(site,i) in sites" style="margin-bottom: 5px;")
                            td
                                | {{ i+1 }}.
                            td
                                input.form-control(v-model="site.domain")
                            td
                                input.form-control(v-model="site.token" disabled)
                            td
                                button.btn(v-on:click="remove_site(site)") {{ $t('template.Remove') }}
                    button.btn(v-on:click="add_site()") {{ $t('template.Add') }}
        div.row
            div.col-12
                button.btn.btn-diga(v-on:click="save_settings") {{ $t('template.Save') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    data() {
        return {
            removed_sites: [],
            sites: null,
            global_settings: null,
        }
    },
    watch: {
        sites_global(){
            this.sites = JSON.parse(JSON.stringify(this.sites_global));
        },
        global_settings_global(){
            this.global_settings = JSON.parse(JSON.stringify(this.global_settings_global));
        },
    },
    created(){
        this.global_settings = JSON.parse(JSON.stringify(this.global_settings_global));
        this.sites = JSON.parse(JSON.stringify(this.sites_global));
    },
    computed: {
        ...mapGetters({
            states: 'getNotRemovedServiceStates',
            users: 'getUsers',
            event_types: 'getEventTypes',
            sites_global: 'getSites',
            global_settings_global: 'getGlobalSettings',
        }),
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.SiteSettings');
    },
    methods: {
        save_settings(){
            let $this = this;
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                let payload = JSON.parse(JSON.stringify(this.$data));
                this.$root.global_loading = true;
                this.$http.post('/api/settings/sites', payload).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("template.Settings_saved"), this.$root.$t("template.Success"));
                        this.removed_sites = [];
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                });
            });
        },
        remove_site(site){
            if (confirm(this.$root.$t("template.Sure_remove_site"))){
                this.removed_sites.push(site.id);
                let index = this.sites.indexOf(site);
                this.sites.splice(index, 1);
            }
        },
        add_site(){
            let site = {
                id: 0,
                domain: '',
            };
            this.sites.push(site);
        },
    },
}
</script>