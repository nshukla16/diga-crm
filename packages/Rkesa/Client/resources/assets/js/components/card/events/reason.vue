<style>

</style>

<template lang="pug">
    div.modal.fade#modal-event-reason(tabindex="-1" aria-hidden="true")
        div.modal-dialog(role="document")
            div.modal-content
                div.modal-header
                    h5.modal-title {{ $t("client.Reason") }}
                    button(type="button" class="close" data-dismiss="modal" aria-label="Close")
                        span(aria-hidden="true") &times;
                div.modal-body
                    fieldset.form-group(:class="{ 'has-error': errors.has('reason') }")
                        input.form-control(type="text" name="reason" v-validate="'required'" v-on:keyup.enter="reason_why_not_new_ok" v-model="reason_why_not_new_text" v-bind:data-vv-as="$t('client.Reason').toLowerCase()")
                        span.help-block(v-show="errors.has('reason')") {{ errors.first('reason') }}
                div.modal-footer(style="justify-content: center;")
                    button.btn.green(v-on:click="reason_why_not_new_ok")
                        span(v-show="!reason_loading") OK
                        div(v-show="reason_loading")
                            div.loader.sm-loader
</template>

<script>
export default {
    props: ['event'],
    data: function(){
        return {
            loading: false,
            reason_why_not_new_text: null,
            reason_loading: false,
        }
    },

    mounted(){

    },
    methods: {
        reason_why_not_new_ok: function(){
            //                if (!this.reason_loading) {
            //                    this.$validator.validateAll().then(result => {
            //                        if (!result) {
            //                            this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
            //                            return;
            //                        }
            //                        this.reason_loading = true;
            //
            //                    });
            //                }
        },
    },
}
</script>