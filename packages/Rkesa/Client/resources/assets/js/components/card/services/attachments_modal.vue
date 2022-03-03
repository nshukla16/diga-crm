<style>

</style>

<template lang="pug">
    div.modal.fade.modal-medium.modal-attachments(tabindex="-1" aria-hidden="true")
        div.modal-dialog.modal-lg.modal-dialog-centered(role="document")
            div.modal-content
                div.modal-body(v-if="service")
                    div(style="text-align: center;font-size: 20px;margin: 20px 20px 10px;")
                        | {{ $t('client.Attachments') }}
                    div(style="overflow: hidden;" v-if="service.attachments.length + service.checklist_filleds.length > 0")
                        h5 {{ $t('client.Attachments') }}
                        div.row(style="margin-top: 10px;" v-for="i in Math.ceil(service.attachments.length / 4)")
                            div.col-3(style="text-align: center;" v-for="attachment in service.attachments.slice((i - 1) * 4, i * 4)")
                                a(v-bind:href="attachment.file" target="_blank")
                                    div(style="margin-bottom: 5px;height: 40px;")
                                        i.fa.fa-file-text-o(style="font-size: 40px;line-height: 40px;")
                                    div {{ attachment.name }}
                                a(href="#", @click.prevent="remove_attachment(attachment)")
                                    i.fa.fa-times
                        h5 {{ $t('template.Checklists') }}
                        div.row(style="margin-top: 10px;" v-for="i in Math.ceil(service.checklist_filleds.length / 4)")
                            div.col-3(style="text-align: center;" v-for="fill in service.checklist_filleds.slice((i - 1) * 4, i * 4)")
                                router-link(:to="{name: 'fill_show', params: {id: fill.id}}" target="_blank")
                                    div(style="margin-bottom: 5px;height: 40px;")
                                        i.fa.fa-file-text-o(style="font-size: 40px;line-height: 40px;")
                                    div {{ fill.checklist.name }} ({{ fill.created_at }})
                    div(v-else)
                        div(style="margin: 10px 20px 0; color: #929292;") {{ $t('client.There_is_no_attachments_yet') }}
                    div(style="margin-top: 20px;")
                        vue-core-image-upload(
                            :class="['btn', 'btn-diga']",
                            @imageuploading="imageuploading",
                            @imageuploaded="imageuploaded",
                            @errorhandle="imageerror",
                            :headers="{Authorization: $root.access_token}",
                            :extensions="'*'",
                            :inputAccept="'*'",
                            :max-file-size="$root.max_file_size",
                            :text="$t('client.Upload_file')",
                            url="/api/file_upload")
</template>

<script>
export default {
    props: ['service'],
    data(){
        return {

        }
    },
    methods: {
        remove_attachment(attachment){
            if (confirm(this.$root.$t('client.Are_you_sure_you_want_to_delete_attachment'))) {
                this.$http.post('/api/services/' + this.service.id + '/remove_attachment', {id: attachment.id}).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        let index = this.service.attachments.indexOf(attachment);
                        this.service.attachments.splice(index, 1);
                        this.$toastr.s(this.$root.$t('client.Attachment_removed'), this.$root.$t("template.Success"));
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                });
            }
        },
        imageuploading: function(){
            //                this.loading = true;
        },
        imageuploaded: function(res){
            //                this.loading = false;
            if (res.errcode == 0) {
                let attachment = {
                    name: res.name,
                    file: res.url,
                };
                this.$http.post('/api/services/' + this.service.id + '/attachment', { attachment: attachment }).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        attachment.file = res.data.file;
                        attachment.id = res.data.id;
                        this.service.attachments.push(attachment);
                        this.$toastr.s(this.$root.$t('client.File_uploaded'), this.$root.$t("template.Success"));
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                });
            } else {
                this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
            }
        },
        imageerror(e){
            this.$toastr.e(e, this.$root.$t("template.Error"));
        },
    },
}
</script>