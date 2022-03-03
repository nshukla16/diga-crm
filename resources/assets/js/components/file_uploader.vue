<style>
    .upload_tabs_container{
        display: flex;
    }
    .upload_tabs_container > div {
        text-align: center;
        background-color: #24C5C3;
        padding: 10px 0;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.5);
        transition: all .1s;
        color: #FFF;
        cursor: pointer;
        flex: 1;
    }
    .upload_tabs_container > div.tab_active {
        background-color: #FFF !important;
        color: #2A6668;
    }
    .upload_tabs_container > div:first-child {
        border-top-left-radius: 5px;
    }
    .upload_tabs_container > div:last-child {
        border-top-right-radius: 5px;
    }
    .upload_tabs_container > div:hover {
        background-color: #25b5c5;
    }
    .drop-area{
        height: 400px;
    }
    .drop-area > div{
        height: 100%;
    }
    .drop-area.highlight{
        border: 3px dashed #c5c5c5;
        border-radius: 0 0 10px 10px;
    }
    .myttt{
        height: 100%;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .progress-wrapper{
        display: flex;
        justify-content: center;
        padding: 20px;
        flex-direction: column;
    }
    .progress-wrapper .loading-bar{
        margin-top: 10px;
        height: 20px;
        background-color: #24C5C3;
        border-radius: 5px;
        text-align: center;
    }
</style>

<template lang="pug">
    div.d-inline-block
        nobr(v-if="file_url != null")
            a.short-link(:href="file_url" target="_blank", :title="file_name") {{ file_name }}
            a.ml-2(href="#", @click.prevent="$emit('remove')", v-if="editable")
                i.fa.fa-times
        button.btn.btn-diga(v-else @click="open_modal" :disabled="!editable") {{ $t('template.Upload') }}
        portal(to="uploader-destination")
            div.modal.fade(:id="'file-uploader-modal-' + _uid" tabindex="-1" role="dialog" aria-hidden="true")
                div.modal-dialog(role="document")
                    div.modal-content
                        div.upload_tabs_container
                            div(v-on:click="setActive('first')", :class="{ tab_active: isActive('first') }") {{ $t('template.Upload_to_server') }}
                            div(v-on:click="setActive('second')", :class="{ tab_active: isActive('second') }" v-if="$root.global_settings.gd_enabled") {{ $t('template.Upload_to_gd') }}
                        div.drop-area(:id="'drop-area-'+_uid")
                            input.inputfile(:id="'file-' + _uid" type="file" name="file" :accept="extensions" style="display:none;")
                            div(v-if="isActive('first')")
                                div.d-flex.myttt
                                    span {{ $t('template.Move_the_file_here') }}
                                    span {{ $t('template.or') }}
                                    label.btn.btn-diga(:for="'file-' + _uid") {{ $t('template.Choose') }}
                                    span(style="margin-top: 10px; font-size: 14px;") {{ $t('template.Server_max_file_size') }}
                            div(v-if="isActive('second') && $root.global_settings.gd_enabled" )
                                div.d-flex.myttt
                                    span {{ $t('template.Move_the_file_here') }}
                                    span {{ $t('template.or') }}
                                    label.btn.btn-diga(:for="'file-' + _uid") {{ $t('template.Choose') }}
                                    span(style="margin-top: 10px; font-size: 14px;") {{ $t('template.GD_max_file_size') }}
                            div.progress-wrapper(v-if="isActive('third')")
                                div.text-center(v-if="step === 1") {{ $t('template.Upload_step_1') }}
                                div.text-center(v-if="step === 2") {{ $t('template.Upload_step_2') }}
                                div.text-center(v-if="step === 3") {{ $t('template.Upload_step_3') }}
                                div.loading-bar(:style="{width: progress+'%'}") {{ progress + ' %' }}
</template>

<script>
export default {
    props: {
        file_url: String,
        file_name: String,
        editable: Boolean,
        extensions: { // should be with point (.), for example: ".doc,.pdf"
            type: String,
            default: '*',
        },
    },
    data(){
        return {
            activeItem: 'first',
            progress: 0,
            step: 1,
            progressTimeoutId: null,
            max_gd_size: 1073741824, // 1 Gb in bytes
        }
    },
    computed: {
        max_size(){
            return this.$root.max_file_size;
        },
        drop_area_sel(){
            return '#drop-area-' + this._uid;
        },
        file_sel(){
            return '#file-' + this._uid;
        },
        uploader_sel(){
            return '#file-uploader-modal-' + this._uid;
        },
    },
    mounted(){
        let $this = this;

        // Prevent default behavior on drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            $(document).on(eventName, this.drop_area_sel, this.preventDefaults);
        });

        // Highlighting
        ['dragenter', 'dragover'].forEach(eventName => {
            $(document).on(eventName, this.drop_area_sel, this.highlight);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            $(document).on(eventName, this.drop_area_sel, this.unhighlight);
        });

        // Attaching listeners
        $(document).on('drop', this.drop_area_sel, function(e){
            $this.handleFiles(e.originalEvent.dataTransfer.files);
        });

        $(document).on('change', this.file_sel, function(e){
            $this.handleFiles(this.files)
        });
    },
    methods: {
        // For tabs
        isActive: function (menuItem) {
            return this.activeItem === menuItem
        },
        setActive: function (menuItem) {
            this.activeItem = menuItem;
        },
        //
        open_modal(){
            this.progress = 0;
            this.step = 1;
            this.job_id = null;
            this.setActive('first');
            $(this.uploader_sel).modal('show');
        },
        preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        },
        highlight(e) {
            let drop_area = document.getElementById('drop-area-' + this._uid);
            drop_area.classList.add('highlight')
        },
        unhighlight(e) {
            let drop_area = document.getElementById('drop-area-' + this._uid);
            drop_area.classList.remove('highlight')
        },
        handleFiles(files) {
            if ([...files].length !== 1){
                this.$toastr.e(this.$root.$t("template.Not_possible_to_upload_multiple_files"), this.$root.$t("template.Error"));
            } else {
                let file = files[0];
                let type = this.isActive('first') ? 1 : 2;

                let ext = file.name.split('.').pop();
                if (this.extensions !== '*' && !this.extensions.split(',').includes(ext)){
                    this.$toastr.e(this.$root.$t("template.Not_allowed_extension"), this.$root.$t("template.Error"));
                } else {
                    if (type === 1 && file.size > this.max_size) {
                        this.$toastr.e(this.$root.$t("template.Filesize_is_big"), this.$root.$t("template.Error"));
                    } else if (type === 2 && file.size > this.max_gd_size) {
                        this.$toastr.e(this.$root.$t("template.Filesize_is_big"), this.$root.$t("template.Error"));
                    } else {
                        this.upload_file(file, type);
                    }
                }
            }
        },
        upload_file(file, type){ // type: 1 - server, 2 - google drive
            this.setActive('third');

            let formData = new FormData();
            formData.append('files', file);

            let url = '';
            if (type === 1){
                url = '/api/file_upload';
            } else {
                url = '/api/settings/integrations/google_drive/upload_with_job';
            }

            let $this = this;

            return this.$http.post(url, formData, {
                progress(e) {
                    if (e.lengthComputable) {
                        $this.progress = Math.round(e.loaded / e.total * 100);
                    }
                },
            }).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    if (type === 1) {
                        this.$emit('finished', [res.data.url, res.data.name]);
                        $(this.uploader_sel).modal('hide');
                    } else {
                        this.step = 2;
                        this.progress = 0;
                        this.progressTimeout(res.data.job_id);
                    }
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
        progressTimeout(job_id){
            let $this = this;
            this.progressTimeoutId = setInterval(() => {
                $this.progressGoogleDrive(job_id);
            }, 1000);
        },
        progressGoogleDrive(job_id){
            this.$http.get('/api/settings/integrations/google_drive/job_status/' + job_id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    clearInterval(this.progressTimeoutId);
                    this.progressTimeoutId = null;
                } else {
                    this.progress = res.data.progress;
                    if (this.progress == 100){
                        this.step = 3;
                    }
                    if (this.progress == 100 && res.data.url){
                        clearInterval(this.progressTimeoutId);
                        this.progressTimeoutId = null;

                        this.$emit('finished', [res.data.url, res.data.name]);
                        $(this.uploader_sel).modal('hide');
                    }
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                clearInterval(this.progressTimeoutId);
                this.progressTimeoutId = null;
            });
        },
    },
}
</script>