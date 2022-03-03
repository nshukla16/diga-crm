<style>


</style>

<template lang="pug">
    div
        div.modal.fade#common-call-window(tabindex="-1" aria-hidden="true")
            div.modal-dialog(role="document")
                div.modal-content
                    div.modal-header
                        h5.modal-title {{ $t("zadarma.Call") }}
                        button(type="button" class="close" data-dismiss="modal" aria-label="Close")
                            span(aria-hidden="true") &times;
                    div.modal-body
                        h3(v-text="$store.getters['call/getNumber']")
                    div.modal-footer(style="justify-content: center;")
                        button.btn.green(v-on:click="make_call")
                            span(v-show="!calling") {{ $t("zadarma.Call") }}
                            div(v-show="calling")
                                div.loader.sm-loader
</template>

<script>
import { mapState } from 'vuex';
export default {
    data() {
        return {
            modal_id: '#common-call-window',
            calling: false,
        }
    },
    computed: {
        ...mapState('call', ['opened']),
    },
    watch: {
        opened(v) {
            let popup = jQuery(this.modal_id);
            if (v) popup.modal('show');
            else popup.modal('hide');
        },
    },
    mounted() {
        let $this = this;
        jQuery(this.modal_id).on("hide.bs.modal", function () {
            setTimeout(() => {
                $this.$store.dispatch('call/closeWindow');
            }, 500);
        });
    },
    methods: {
        make_call() {
            this.calling = true;
            let $this = this;
            let phone_number = $this.$store.getters['call/getNumber'];
            this.$http.post('/api/zadarma/callback', { phone_number: phone_number }).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    $this.calling = false;
                } else {
                    setTimeout(function () {
                        $this.calling = false;
                    }, 5000);
                }
            })
        },
    },
}
</script>