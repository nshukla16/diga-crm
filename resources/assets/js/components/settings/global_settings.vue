<style>
    .info{
        vertical-align: top;
        padding-left: 20px;
    }
</style>

<template lang="pug">
    div(v-if="global_settings")
        div.row
            div.col-12.col-md-6.mb-3
                div.diga-container.p-4
                    h2 {{ $t('template.Common_settings') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Site_name') }}
                                input.form-control(v-model="global_settings.site_name")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Site_name_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Company_type') }}
                                select.form-control(v-model="global_settings.company_type")
                                    option(value="1") {{ $t('template.Company_type_service') }}
                                    option(value="2") {{ $t('template.Company_type_product') }}
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Company_type_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Head') }}
                                textarea.form-control(v-model="global_settings.head")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Head_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Currency') }}
                                select.form-control(v-model="global_settings.currency")
                                    option(v-for="currency in $root.currencies", :value="currency.code") {{ currency.name + ' (' + currency.code + ')' }}
                        div.col-12.col-sm-6.info
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Default_language') }}
                                select.form-control(v-model="global_settings.default_language")
                                    option(value="ru") Русский
                                    option(value="pt") Português
                                    option(value="en") English
                                    option(value="es") Espanol
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Default_language_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Timezone') }}
                                select.form-control(v-model="global_settings.timezone")
                                    option(v-for="tz in timezones", :value="tz.tzCode") {{ tz.label }}
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Timezone_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.First_day_of_week') }}
                                select.form-control(v-model="global_settings.first_day_of_week")
                                    option(:value="1") {{ $t('template.days_full')[0] }}
                                    option(:value="7") {{ $t('template.days_full')[6] }}
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Timezone_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label {{ $t('template.Enable_companies') }}
                                input.ml-2(type="checkbox" v-model="global_settings.enable_companies")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Enable_companies_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label {{ $t('template.Disable_service_address') }}
                                input.ml-2(type="checkbox" v-model="global_settings.disable_service_address")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Disable_service_address_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            button.btn.btn-diga(@click="import_data") {{ $t('template.Import') }}
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Import_desc') }}
                    import_window()
                    div.row.mt-2
                        div.col-12.col-sm-6
                            button.btn.btn-diga(@click="clear_popup_notifications") {{ $t('template.Clear_popup_notifications') }}
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Client_popup_notifications_info') }}
                    h2 {{ $t('template.Smtp_settings') }}
                    div.row
                        div.col-6.col-sm-6
                            label.control-label {{ $t('template.Use_default_smtp_settings') }}
                        div.col-6.col-sm-6
                                div(style="width:120px;")
                                    bootstrap-toggle(data-size="mini" v-model="global_settings.use_default_smtp", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="120", data-height="38", data-onstyle="default")
                    div(v-if='!global_settings.use_default_smtp')
                        div.row
                            div.col-12.col-sm-6
                                fieldset.form-group
                                    label.control-label {{ $t('template.Mail_host') }}
                                    input.form-control(v-model="global_settings.smtp_settings.mail_host")
                        div.row
                            div.col-12.col-sm-6
                                fieldset.form-group
                                    label.control-label {{ $t('template.Mail_port') }}
                                    input.form-control(v-model="global_settings.smtp_settings.mail_port")
                        div.row
                            div.col-12.col-sm-6
                                fieldset.form-group
                                    label.control-label {{ $t('template.Mail_username') }}
                                    input.form-control(v-model="global_settings.smtp_settings.mail_username")
                        div.row
                            div.col-12.col-sm-6
                                fieldset.form-group
                                    label.control-label {{ $t('template.Mail_password') }}
                                    input.form-control(v-model="global_settings.smtp_settings.mail_password" type="password")
                        div.row
                            div.col-12.col-sm-6
                                fieldset.form-group
                                    label.control-label {{ $t('template.Mail_encryption') }}
                                    select.form-control(v-model="global_settings.smtp_settings.mail_encryption")
                                        option(value="ssl") SSL
                                        option(value="tls") TLS
                    h2 {{ $t('template.invoice_auto_send_email') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Mail_username') }}
                                input.form-control(v-model="global_settings.invoice_auto_send_email")
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Mail_password') }}
                                input.form-control(v-model="global_settings.invoice_auto_send_email_password")

            div.col-12.col-md-6.mb-3
                div.diga-container.p-4
                    h2 {{ $t('template.Visual_settings') }}
                    div.row.mb-3
                        div.col-12.col-sm-6
                            div
                                label.control-label {{ $t('template.Company_logo') }}
                            img(:src="src", style="max-width: 300px; width: 100%;background-image: url(/img/transparent.png)")
                            br
                            vue-core-image-upload(
                                style="max-width: 300px; width: 100%;",
                                :class="['btn', 'green', 'text-center']",
                                :crop="false",
                                @imageuploading="imageuploading",
                                @imageuploaded="imageuploaded",
                                @errorhandle="imageerror",
                                :headers="{Authorization: $root.access_token}",
                                :extensions="'png,gif,jpeg,jpg'",
                                :max-file-size="$root.max_file_size",
                                :text="$t('hr.Upload_image')",
                                url="/api/photo_upload")
                            div(v-show="loading")
                                div.loader.sm-loader
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Company_logo_desc') }}
                    //div.row.mb-3
                    //    div.col-12.col-sm-6
                    //        div
                    //            label.control-label {{ $t('template.Background_image') }}
                    //        img(:src="src2", style="max-width: 300px; width: 100%;background-image: url(/img/transparent.png)")
                    //        br
                    //        vue-core-image-upload(
                    //            style="max-width: 300px; width: 100%;",
                    //            :class="['btn', 'green', 'text-center']",
                    //            :crop="false",
                    //            @imageuploading="imageuploading2",
                    //            @imageuploaded="imageuploaded2",
                    //            @errorhandle="imageerror2",
                    //            :headers="{Authorization: $root.access_token}",
                    //            :extensions="'png,gif,jpeg,jpg'",
                    //            :max-file-size="$root.max_file_size",
                    //            :text="$t('hr.Upload_image')",
                    //            url="/photo_upload")
                    //        div(v-show="loading2")
                    //            div.loader.sm-loader
                    //    div.col-12.col-sm-6.info
                    //        i.fa.fa-question-circle-o.mr-2
                    //        | {{ $t('template.Background_image_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Color1') }}
                                div.position-relative
                                    sketch-picker(v-if="selected[0]" v-model="color1" v-on-clickaway="hide_picker")
                                    div.color-icon.color(v-bind:style="{'background-color': color1.hex}" v-on:click="show_picker(0)")
                                    input.form-control.settings-inputs(style="width: 100px;" v-model="color1.hex")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Color1_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Color2') }}
                                div.position-relative
                                    sketch-picker(v-if="selected[1]" v-model="color2" v-on-clickaway="hide_picker")
                                    div.color-icon.color(v-bind:style="{'background-color': color2.hex}" v-on:click="show_picker(1)")
                                    input.form-control.settings-inputs(style="width: 100px;" v-model="color2.hex")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Color2_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Color3') }}
                                div.position-relative
                                    sketch-picker(v-if="selected[2]" v-model="color3" v-on-clickaway="hide_picker")
                                    div.color-icon.color(v-bind:style="{'background-color': color3.hex}" v-on:click="show_picker(2)")
                                    input.form-control.settings-inputs(style="width: 100px;" v-model="color3.hex")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Color3_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Color4') }}
                                div.position-relative
                                    sketch-picker(v-if="selected[3]" v-model="color4" v-on-clickaway="hide_picker")
                                    div.color-icon.color(v-bind:style="{'background-color': color4.hex}" v-on:click="show_picker(3)")
                                    input.form-control.settings-inputs(style="width: 100px;" v-model="color4.hex")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Color4_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Color5') }}
                                div.position-relative
                                    sketch-picker(v-if="selected[4]" v-model="color5" v-on-clickaway="hide_picker")
                                    div.color-icon.color(v-bind:style="{'background-color': color5.hex}" v-on:click="show_picker(4)")
                                    input.form-control.settings-inputs(style="width: 100px;" v-model="color5.hex")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o.mr-2
                            | {{ $t('template.Color5_desc') }}
        div.row
            div.col-12.d-flex.justify-content-between
                button.btn.btn-diga(v-on:click="save_settings()") {{ $t('template.Save') }}
                span ver. {{ global_settings.erp_version }}
</template>

<script>
import import_window from './import_window';
import timezones from 'compact-timezone-list';

export default {
    data() {
        return {
            global_settings: null,
            loading: false,
            src: null,
            loading2: false,
            src2: null,
            selected: [],
            color1: { hex: null},
            color2: { hex: null},
            color3: { hex: null},
            color4: { hex: null},
            color5: { hex: null},
            timezones: timezones,
        }
    },
    components: {
        import_window,
    },
    methods: {
        clear_popup_notifications(){
            this.$http.post('/api/clear_popups').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.$toastr.removeByType("success");
                    this.$toastr.s(this.$root.$t("template.Popups_cleared"), this.$root.$t("template.Success"));
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        import_data(){
            $('#modal-import').modal('show');
        },
        save_settings(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.$root.global_loading = true;
                let payload = Object.assign({}, this.global_settings);
                payload.site_logo = this.src;
                payload.background_image = this.src2;
                payload.color1 = this.color1.hex;
                payload.color2 = this.color2.hex;
                payload.color3 = this.color3.hex;
                payload.color4 = this.color4.hex;
                payload.color5 = this.color5.hex;
                this.$http.post('/api/global_settings', payload).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("template.Settings_saved"), this.$root.$t("template.Success"));
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                });
            });
        },
        show_picker(i){
            Vue.set(this.selected, i, true);
        },
        hide_picker(){
            for (let i = 0; i < 5; i++) {
                Vue.set(this.selected, i, false);
            }
        },
        imageuploading() {
            this.loading = true;
        },
        imageuploaded(res) {
            this.loading = false;
            if (res.errcode == 0) {
                this.src = res.url;
            } else {
                this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
            }
        },
        imageerror(e){
            this.loading = false;
            this.$toastr.e(e, this.$root.$t("template.Error"));
        },
        imageuploading2() {
            this.loading2 = true;
        },
        imageuploaded2(res) {
            this.loading2 = false;
            if (res.errcode == 0) {
                this.src2 = res.url;
            } else {
                this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
            }
        },
        imageerror2(e){
            this.loading2 = false;
            this.$toastr.e(e, this.$root.$t("template.Error"));
        },
    },
    mounted(){
        this.global_settings = this.$store.getters.getGlobalSettings;
        this.src = this.global_settings.site_logo;
        this.src2 = this.global_settings.background_image;
        for (let i = 0; i < 5; i++) {
            this.selected.push(false);
        }
        this.color1 = { hex: this.global_settings.color1 };
        this.color2 = { hex: this.global_settings.color2 };
        this.color3 = { hex: this.global_settings.color3 };
        this.color4 = { hex: this.global_settings.color4 };
        this.color5 = { hex: this.global_settings.color5 };
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.GlobalSettings');
    },
}
</script>