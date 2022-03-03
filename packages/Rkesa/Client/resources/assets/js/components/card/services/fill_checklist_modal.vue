<style>

</style>

<template lang="pug">
    div.modal.fade(tabindex="-1" aria-hidden="true")
        div.modal-dialog.modal-lg.modal-dialog-centered(role="document")
            div.modal-content(v-if="checklist")
                div.modal-header
                    h5 {{ checklist.name }}
                div.modal-body
                    table.table.table-striped.table-hover
                        thead
                            tr
                                td.text-center {{ $t('calendar.Questions') }}
                                td.text-center {{ $t('calendar.Answers') }}
                        tbody
                            tr(v-for="entity in checklist.checklist_entities")
                                td
                                    span {{ entity.name }}
                                td
                                    textarea.form-control(v-model="entity.answer")
                    label {{ $t('template.Note') }}
                    textarea.form-control.mb-2(v-model="checklist.filled_description")
                    div(style="text-align:center;")
                        button.btn.default.green(v-on:click="fill_checklist_ok") {{ $t("template.Save") }}
</template>

<script>
export default {
    props: ['checklist', 'service'],
    data() {
        return {

        }
    },
    methods: {
        fill_checklist_ok: function(){
            let payload = Object.assign({}, this.checklist);
            payload.service_id = this.service.id;
            this.$http.post('/api/fills', payload).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.$toastr.s(this.$root.$t("calendar.Checklist_saved"), this.$root.$t("template.Success"));
                    this.service.checklist_filleds.push(res.data.checklist);
                    jQuery('#fill_checklist_modal').modal('hide');
                    this.$bus.$emit('resolve_fill_checklist');
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
    },
}
</script>