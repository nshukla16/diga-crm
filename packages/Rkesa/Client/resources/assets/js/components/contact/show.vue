<style>

</style>

<template lang="pug">
    div(v-if="contact")
        //div.alert.alert-danger(v-if="no_tasks" role="alert")
            | {{ $t('client.There_is_no_tasks_in_client') }}
            a.ml-2(v-bind:href="'/clients/' + card.client_id + '/ignore_no_tasks'") {{ $t('client.Ignore') }}
        client(v-if="$root.enable_companies && contact.client", :client="contact.client")
        contact(:contact="contact")
        card(:services="contact.services",
            :events="contact.events",
            :contact="contact",
            :history_entities="contact.client_history",
            :company_id="contact.client ? contact.client.id : null",
            :projects="contact.client ? contact.client.projects : []",
            :equipment="contact.client ? contact.client.equipment : []",
            :selected_service="service_id")
</template>

<script>
import client from '../shared/client.vue'
import contact from '../shared/contact.vue'
import card from '../card/main.vue'

export default {
    props: ['service_id', 'id'],
    components: {
        client, contact, card,
    },
    data: function() {
        return {
            contact: null,
        }
    },
    methods: {
        load_contact: function(){
            let $this = this;
            this.$root.global_loading = true;
            this.$http.get('/api/contacts/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                } else {
                    this.contact = res.data;
                    this.contact.services.forEach(function(service, i){
                        $this.contact.services[i].client_contact = $this.contact;
                    });
                    this.contact.events.forEach(function(event, i){
                        $this.contact.events[i].client_contact = $this.contact;
                    });
                    this.$root.global_loading = false;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
    },
    computed: {
        main_contact: function(){
            return this.client.client_contacts.filter(function(contact){ return contact.is_main_contact })[0];
        },
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('client.Client_card');
        this.load_contact();
        // this.$bus.$on('refetch_client', (data) => {
        //     this.load_contact()
        // })
    },
    beforeDestroy: function() {
        // this.$bus.$off("refetch_client", this.load_contact);
    },
}
</script>
