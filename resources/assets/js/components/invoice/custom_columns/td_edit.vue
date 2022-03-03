<template lang="pug">
    div
        div.modal.fade(:id="'modal-cancel-reason-'+row.id" tabindex="-1" aria-hidden="true")
            div.modal-dialog.modal-dialog-centered(role="document")
                div.modal-content
                    div.modal-header {{ $t('template.cancel_invoice')}}
                    div.modal-body
                        div {{ $t("template.write_the_cancel_reason") }}:
                        textarea(v-model="canceling_reason" style="width: 100%;")
                        div(style="text-align: center")
                            button.btn.green(:disabled="canceling_reason === ''" v-on:click="remove_invoice(row.id)") OK

        //- router-link.btn.btn-secondary(:event="$root.can_do('invoices', 'update') !== 0 ? 'click' : ''" style="margin-right: 5px", :to="{name: 'invoice_edit', params: {id: row.id}}") {{ $t('service.Service_edit') }}
        span(v-if="row.is_canceled === true") {{$t('template.invoice_canceled')}}
        button.btn.btn-danger(v-if="row.is_canceled !== true" :disabled="$root.can_do('invoices', 'delete') === 0" v-on:click="show_reason") {{ $t('template.cancel_invoice') }}
</template>

<script>
export default {
    props: ['row'],
    data(){
        return {
            canceling_reason: ""
        }
    },
    methods: {
        show_reason(){
            if (this.row.is_exported === true){
                if (confirm(this.$root.$t('template.this_invoice_has_already_been_exported'))) {
                    jQuery('#modal-cancel-reason-' + this.row.id).modal('show');
                }
            }
            else{
                jQuery('#modal-cancel-reason-' + this.row.id).modal('show');
            }
        },
        remove_invoice(id) {
            if (confirm(this.$root.$t('calendar.AreYouSure'))) {
                this.$http.post('/api/invoices/cancel/' + id, {}).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("template.History_deleted"), this.$root.$t("template.Success"));
                        jQuery('#modal-cancel-reason-' + this.row.id).modal('hide');
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