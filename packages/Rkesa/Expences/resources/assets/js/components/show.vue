<style>

</style>

<template lang="pug">
    div(v-if="expence")
        section.diga-container.p-3.mb-3
            .info-top
                div.caption.float-left
                    span \#{{ expence.id }}
                    router-link.contacts-list.light-link.active.ml-2(:to="{ name: 'expences_show', params: {id: expence.id }}") {{ expence.name }}
                div.float-right(style="font-size: 18pt;")
                    router-link.btn.btn-circle.btn-default-1(:to="{ name: 'expences_edit', params: {id: expence.id }}")
                        i.fa.fa-pencil
                div.clearfix
            .info-bottom
                .row
                    section.col-12.col-md-4
                        div.d-flex
                            span.text-muted {{ $t('project.Invoice') }}
                            span.dotter
                            span.text-right {{ expence.invoice_number }}
                        div.d-flex
                            span.text-muted {{ $t('expences.supplier') }}
                            span.dotter
                            span.text-right {{ expence.supplier }}
                    section.col-12.col-md-4
                        div.d-flex
                            span.text-muted {{ $t('project.Date') }}
                            span.dotter
                            span.text-right {{ expence.date }}
                        div.d-flex
                            span.text-muted {{ $t('client.base_value') }}
                            span.dotter
                            span.text-right {{ expence.total_without_vat }}
                    section.col-12.col-md-4
                        div.d-flex
                            span.text-muted {{ $t('client.type_of_the_vat') }}
                            span.dotter
                            span.text-right {{ expence.vat_type }}%
                        div.d-flex
                            span.text-muted {{ $t('client.total') }}
                            span.dotter
                            span.text-right {{ expence.total }}
                        div.d-flex
                            span.text-muted {{ $t('client.Correspondent_account') }}
                            span.dotter
                            a(v-if="expence.invoice_file" :href="expence.invoice_file" target="_blank") {{expence.invoice_file_name}}

</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['id'],
    data: function(){
        return {
            expence: null,
        }
    },
    created() {
        this.load_expence();
    },
    computed: {
    },
    methods: {
        load_expence(){
            this.$root.global_loading = true;
            this.$http.get('/api/expences/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.expence = res.data;
                    document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.expence') + ': ' + this.expence.invoice_number;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        }
    },
}
</script>