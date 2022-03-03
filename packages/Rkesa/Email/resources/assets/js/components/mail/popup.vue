<style>
    .modal-mail .modal-dialog{
        max-width: 1026px;
    }
</style>
<template lang="pug">
div.modal.fade.modal-mail(id="modal-mail-general" tabindex="-1" aria-hidden="true")
    div.modal-dialog.modal-dialog-centered(role="document")
        div.modal-content
            div
                iframe(v-if="email_link", :src="email_link", name="email_iframe", id="email_iframe", @load="email_service_iframe_loaded", style="width: 100%; min-height: 736px; border: 0px;") 
</template>

<script>
export default {
    props: ['email_link', 'redirect_to'],
    methods: {
        email_service_iframe_loaded: function(){
            let $this = this;
            jQuery('#email_iframe').ready(function(){
                window.frames["email_iframe"].App.Screens.oScreens.information.Model.reportVisible.subscribe(function(newValue) {
                    if (newValue){
                        $this.timerId = setInterval(function() {
                            if (!window.frames["email_iframe"].erp_email_sends){
                                clearInterval($this.timerId);
                                jQuery('#modal-mail-general').modal('hide');
                                // if ($this.invoice !== null){
                                //     $this.invoice_email_resolve($this.invoice);
                                //     $this.invoice = null;
                                // }
                                // else{
                                //     $this.emailResolve($this.tmp_global_data);
                                // }                  
                                $this.$toastr.s($this.$root.$t("client.Message_sent"), $this.$root.$t("template.Success"));
                                $this.$router.push({ name: $this.redirect_to});
                            }
                        }, 100);
                    }
                });
            });
        },
    }
}
</script>

<style>

</style>