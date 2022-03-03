<style>
    .referrers-table td{
        padding: 5px;
    }
</style>

<template lang="pug">
    div
        div.row.mb-3
            div.col-12.col-md-6
                div.diga-container.p-4
                    h2 {{ $t('client.Referrers') }}
                    div.table-responsive
                        table.referrers-table.w-100.mb-2
                            tr(v-for="(client_referrer,i) in referrers")
                                td(style="width: 40px;")
                                    | {{ Number(i)+1 }}.
                                td(style="min-width: 200px")
                                    input.form-control(v-model="client_referrer.title")
                                td(style="width: 85px;")
                                    button.btn.red(v-on:click="remove(client_referrer)") {{ $t('template.Remove') }}
                    button.btn(v-on:click="add()") {{ $t('template.Add') }}
            div.col-12.col-md-6
                div.diga-container.p-4
                    h2 {{ $t('client.Additional_fields') }}
                    div(v-if="$root.enable_companies")
                        h3 {{ $t('template.All_companies') }}
                        add-fields-table.mb-2(:ffields="client_attributes")
                    h3 {{ $t('client.Contacts_or_clients') }}
                    add-fields-table(:ffields="contact_attributes")
        div.row
            div.col-12
                button.btn.btn-diga(v-on:click="save_settings()") {{ $t('template.Save') }}
</template>

<script>
import add_fields_table from './add-fields-table.vue'
import {mapGetters} from "vuex";

export default {
    data() {
        return {
            referrers: [],
            removed: [],
            client_attributes: [],
            contact_attributes: [],
        }
    },
    components: {
        'add-fields-table': add_fields_table,
    },
    watch: {
        client_referrers(){
            this.referrers = JSON.parse(JSON.stringify(this.client_referrers));
        },
        g_client_attributes(){
            this.client_attributes = JSON.parse(JSON.stringify(this.g_client_attributes));
        },
        g_contact_attributes(){
            this.contact_attributes = JSON.parse(JSON.stringify(this.g_contact_attributes));
        },
    },
    created() {
        this.referrers = JSON.parse(JSON.stringify(this.client_referrers));
        this.client_attributes = JSON.parse(JSON.stringify(this.g_client_attributes));
        this.contact_attributes = JSON.parse(JSON.stringify(this.g_contact_attributes));
    },
    computed: {
        ...mapGetters({
            client_referrers: 'getClientReferrers',
            global_settings: 'getGlobalSettings',
        }),
        g_client_attributes(){
            return this.global_settings.client_attributes;
        },
        g_contact_attributes(){
            return this.global_settings.contact_attributes;
        },
    },
    methods: {
        save_settings(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                let $this = this;
                let no_options = false;
                this.client_attributes.forEach(function(attr){
                    if (attr.type == 2 && attr.options.length == 0){
                        no_options = true;
                    }
                });
                this.contact_attributes.forEach(function(attr){
                    if (attr.type == 2 && attr.options.length == 0){
                        no_options = true;
                    }
                });
                if (no_options){
                    $this.$toastr.w($this.$root.$t("template.Need_to_fill_options_to_select"), $this.$root.$t("template.Warning"));
                    return;
                }

                let payload = JSON.parse(JSON.stringify(this.$data));
                payload.client_attributes.forEach(function (item, i) {
                    delete payload.client_attributes[i].new;
                });
                payload.contact_attributes.forEach(function (item, i) {
                    delete payload.contact_attributes[i].new;
                });
                this.$http.post('/api/settings/clients', payload).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("template.Settings_saved"), this.$root.$t("template.Success"));
                        this.removed = [];
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            });
        },
        remove(referrer){
            if (confirm(this.$root.$t("client.Are_you_sure_want_to_delete_referrer"))){
                this.removed.push(referrer.id);
                let index = this.referrers.indexOf(referrer);
                this.referrers.splice(index, 1);
            }
        },
        add(){
            let referrer = {
                id: 0,
                title: '',
            };
            this.referrers.push(referrer);
        },
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('client.Client_settings');
    },
}
</script>