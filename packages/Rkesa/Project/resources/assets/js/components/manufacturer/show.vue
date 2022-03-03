<style>

</style>

<template lang="pug">
    div(v-if="manufacturer")
        section.diga-container.p-3.mb-3
            .info-top
                div.caption.float-left
                    span \#{{ manufacturer.id }}
                    router-link.contacts-list.light-link.active.ml-2(:to="{ name: 'manufacturer_show', params: {id: manufacturer.id }}") {{ manufacturer.name }}
                div.float-right(style="font-size: 18pt;")
                    router-link.btn.btn-circle.btn-default-1(:to="{ name: 'manufacturer_edit', params: {id: manufacturer.id }}")
                        i.fa.fa-pencil
                div.clearfix
            .info-bottom
                .row
                    section.col-12.col-md-4
                        div.d-flex
                            span.text-muted {{ $t('project.Name') }}
                            span.dotter
                            span.text-right {{ manufacturer.name }}
                        div.d-flex
                            span.text-muted {{ $t('project.Uploading_address') }}
                            span.dotter
                            span.text-right {{ manufacturer.uploading_address }}
                    section.col-12.col-md-4
                        div.d-flex
                            span.text-muted {{ $t('project.Legal_address') }}
                            span.dotter
                            span.text-right {{ manufacturer.legal_address }}
                        div.d-flex
                            span.text-muted {{ $t('project.Bank_name') }}
                            span.dotter
                            span.text-right {{ manufacturer.bank_name }}
                    section.col-12.col-md-4
                        div.d-flex
                            span.text-muted {{ $t('client.Bic') }}
                            span.dotter
                            span.text-right {{ manufacturer.bic }}
                        div.d-flex
                            span.text-muted {{ $t('client.Checking_account') }}
                            span.dotter
                            span.text-right {{ manufacturer.checking_account }}
                        div.d-flex
                            span.text-muted {{ $t('client.Correspondent_account') }}
                            span.dotter
                            span.text-right {{ manufacturer.correspondent_account }}
        div.row.mb-3
            contacts.col-12(
                :manufacturer="manufacturer"
            )
        div.row
            equipments.col-12.col-md-4(
                v-if="current_section == 1"
                :manufacturer="manufacturer"
                :current_section.sync="current_section"
            )
            projects.col-12.col-md-4(
                v-if="current_section == 2"
                :manufacturer="manufacturer"
                :current_section.sync="current_section"
            )
            orders.col-12.col-md-4(
                :manufacturer="manufacturer"
            )
            contracts.col-12.col-md-4(
                :manufacturer="manufacturer"
            )

</template>

<script>
import {mapGetters} from "vuex";
import contracts from './contracts/index';
import orders from './orders/index';
import equipments from './equipments/index';
import contacts from './contacts/index';
import projects from './projects/index';

export default {
    props: ['id'],
    components: {
        contracts,
        orders,
        equipments,
        contacts,
        projects
    },
    data: function(){
        return {
            manufacturer: null,
            current_section: 1,
        }
    },
    created() {
        this.load_manufacturer();
    },
    mounted(){
        this.$bus.$on("update_current_section", this.update_current_section);
    },
    beforeDestroy: function() {
        this.update_current_section && this.$bus.$off("update_current_section", this.update_current_section);
    },
    computed: {
    },
    methods: {
        // remove_event(){
        //     if (confirm(this.$root.$t('client.Are_you_sure_you_want_to_delete_event'))) {
        //         this.$http.delete('/api/events/' + this.event.id).then(res => {
        //             if (res.data.errcode == 1) {
        //                 this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
        //             } else {
        //                 this.$toastr.s(this.$root.$t("client.Event_removed"), this.$root.$t("template.Success"));
        //                 this.$emit('remove_event');
        //             }
        //         }, res => {
        //             this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
        //         });
        //     }
        // },
        load_manufacturer(){
            this.$root.global_loading = true;
            this.$http.get('/api/manufacturers/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.manufacturer = res.data;
                    document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Manufacturer') + ': ' + this.manufacturer.name;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        update_current_section(e){
            this.current_section = e;
        }
    },
}
</script>