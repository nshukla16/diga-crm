<style>

</style>

<template lang="pug">
    div(v-if="legal_entity")
        section.diga-container.p-3.mb-3
            .info-top
                div.caption.float-left
                    span \#{{ legal_entity.id }}
                    router-link.contacts-list.light-link.active.ml-2(:to="{ name: 'legal_entity_show', params: {id: legal_entity.id }}") {{ legal_entity.name }}
                div.float-right(style="font-size: 18pt;")
                    span.color2-text.color2-border.envelope-button(v-if="$root.user.is_admin" @click="show_templates()" style="padding: 3px 10px; font-size: 16px;") {{ $t("project.Templates") }}
                    router-link.btn.btn-circle.btn-default-1(v-if="$root.can_do('legal_entities', 'update') !== 0" :to="{ name: 'legal_entity_edit', params: {id: legal_entity.id }}")
                        i.fa.fa-pencil
                div.clearfix
            .info-bottom
                .row
                    section.col-12.col-md-4(v-for="i in 3")
                        div.d-flex(v-for="attr in all_attributes.slice((i - 1) * attr_chunk_count, i * attr_chunk_count)")
                            span.text-muted {{ attr.name }}
                            span.dotter
                            span.text-right {{ attr.value }}
        div.row
            div.col-12.col-md-4
                section.diga-container(style="height: 550px;")
                    .portlet-title
                        .caption(style="padding-right: 10px;")
                            a.btn(v-on:click="new_contract()")
                                i.fa.fa-plus
                            span.caption-subject.bold.uppercase {{ $t("project.Contracts") }}
                    .portlet-body
                        div(v-bar="" style="height: 410px;")
                            div
                                div(v-if="legal_entity.contracts.length > 0")
                                    .item.with-gradient(v-for="contract in legal_entity.contracts")
                                        a.float-right(:href="contract.file" target="_blank") {{ contract.file_name }}
                                        span.clickable.clickable-link(v-on:click="edit_contract(contract)") {{ contract.name }}
                                div.empty-filler(v-else) {{ $t('project.No_contracts') }}
        div.modal.fade#legalEntityModal(tabindex="-1" role="dialog" aria-hidden="true")
            div.modal-dialog.modal-lg(role="document")
                div.modal-content
                    div.modal-header
                        h5.modal-title {{ $t('project.Templates') }}
                        button(type="button" class="close" data-dismiss="modal" aria-label="Close" v-on:click="cancel_templates()")
                            span(aria-hidden="true") &times;
                    div.modal-body
                        div.row(v-if="tmp_legal_entity")
                            div.col-12.col-md-6
                                fieldset.form-group
                                    h4 {{ $t("project.Ready_notification_template") }}
                                    template(v-if="tmp_legal_entity.ready_notification_template_file_path != null")
                                        a(:href="tmp_legal_entity.ready_notification_template_file_path" target="_blank") {{ tmp_legal_entity.ready_notification_template_file_name }}
                                        a.ml-2(href="#", @click.prevent="remove_ready_notification()")
                                            i.fa.fa-times
                                    template(v-else)
                                        vue-core-image-upload(
                                            v-show="!ready_notification_template_loading",
                                            :class="['btn', 'btn-diga']",
                                            @imageuploading="ready_notification_template_uploading",
                                            @imageuploaded="ready_notification_template_uploaded",
                                            @errorhandle="uploading_error",
                                            :extensions="'.docx'",
                                            :inputAccept="'.docx'",
                                            :max-file-size="4194304",
                                            :text="$t('template.Upload')",
                                            :headers="{Authorization: $root.access_token}",
                                            url="/api/file_upload")
                                        div(v-show="ready_notification_template_loading")
                                            div.loader.sm-loader
                                fieldset.form-group
                                    h4 {{ $t("project.Commissioning_certificate_template") }}
                                    template(v-if="tmp_legal_entity.commissioning_certificate_template_file_path != null")
                                        a(:href="tmp_legal_entity.commissioning_certificate_template_file_path" target="_blank") {{ tmp_legal_entity.commissioning_certificate_template_file_name }}
                                        a.ml-2(href="#", @click.prevent="remove_commissioning_certificate()")
                                            i.fa.fa-times
                                    template(v-else)
                                        vue-core-image-upload(
                                            v-show="!commissioning_certificate_template_loading",
                                            :class="['btn', 'btn-diga']",
                                            @imageuploading="commissioning_certificate_template_uploading",
                                            @imageuploaded="commissioning_certificate_template_uploaded",
                                            @errorhandle="uploading_error",
                                            :extensions="'.docx'",
                                            :inputAccept="'.docx'",
                                            :max-file-size="4194304",
                                            :text="$t('template.Upload')",
                                            :headers="{Authorization: $root.access_token}",
                                            url="/api/file_upload")
                                        div(v-show="commissioning_certificate_template_loading")
                                            div.loader.sm-loader
                                fieldset.form-group
                                    h4 {{ $t("project.Commissioning_experience_certificate_template") }}
                                    template(v-if="tmp_legal_entity.commissioning_experience_certificate_template_file_path != null")
                                        a(:href="tmp_legal_entity.commissioning_experience_certificate_template_file_path" target="_blank") {{ tmp_legal_entity.commissioning_experience_certificate_template_file_name }}
                                        a.ml-2(href="#", @click.prevent="remove_commissioning_experience_certificate()")
                                            i.fa.fa-times
                                    template(v-else)
                                        vue-core-image-upload(
                                            v-show="!commissioning_experience_certificate_template_loading",
                                            :class="['btn', 'btn-diga']",
                                            @imageuploading="commissioning_experience_certificate_template_uploading",
                                            @imageuploaded="commissioning_experience_certificate_template_uploaded",
                                            @errorhandle="uploading_error",
                                            :extensions="'.docx'",
                                            :inputAccept="'.docx'",
                                            :max-file-size="4194304",
                                            :text="$t('template.Upload')",
                                            :headers="{Authorization: $root.access_token}",
                                            url="/api/file_upload")
                                        div(v-show="commissioning_experience_certificate_template_loading")
                                            div.loader.sm-loader
                                h4 {{ $t('project.Invoice_template') }}
                                fieldset.form-group
                                    label.control-label {{ $t("project.Taxes") }}
                                    input.ml-2(type="checkbox" v-model="tmp_legal_entity.tax_enabled")
                                    div(v-if="tmp_legal_entity.tax_enabled")
                                        label.control-label {{ $t("project.Tax_rate") }}
                                        input.form-control(v-model="tmp_legal_entity.tax_rate")
                                fieldset.form-group
                                    template(v-if="tmp_legal_entity.sign_file_path != null")
                                        a(:href="tmp_legal_entity.sign_file_path" target="_blank") {{ tmp_legal_entity.sign_file_name }}
                                        a.ml-2(href="#", @click.prevent="remove_sign_file()")
                                            i.fa.fa-times
                                    template(v-else)
                                        vue-core-image-upload(
                                            v-show="!sign_file_loading",
                                            :class="['btn', 'btn-diga']",
                                            @imageuploading="sign_file_uploading",
                                            @imageuploaded="sign_file_uploaded",
                                            @errorhandle="uploading_error",
                                            :extensions="'*'",
                                            :inputAccept="'*'",
                                            :max-file-size="4194304",
                                            :text="$t('estimate.Upload_sign')",
                                            :headers="{Authorization: $root.access_token}",
                                            url="/api/file_upload")
                                        div(v-show="sign_file_loading")
                                            div.loader.sm-loader
                            div.col-12.col-md-6
                                div
                                    h4 {{ $t('project.Variables') }}
                                    div ${contract-number} - № {{ $t('project.Of_contract') }}
                                    div ${current-day-n} - Номер текущего дня в месяце
                                    div ${current-month-n} - Номер текущего месяца
                                    div ${current-month-w-p} - Название текущего месяца в родительном падеже
                                    div ${current-year-n} - Номер текущего года
                                    div ${contract-day-n} - Номер дня подписания контракта
                                    div ${contract-month-w-p} - Название месяца подписания контракта в родительном падеже
                                    div ${contract-year-n} - Номер года подписания контракта
                                    div ${buyer-company} - {{ $t('project.Buyer') }}
                                    div ${equipment-name} - Всё оборудование через запятую
                    div.modal-footer(style="justify-content: space-between;")
                        button.btn.grey(v-on:click="cancel_templates()") {{ $t("template.Cancel") }}
                        button.btn.btn-diga.float-right(v-show="!loading" v-on:click="save_templates()") {{ $t("template.Save") }}
                        div(v-show="loading" style="float: right;margin-top: 10px;margin-right: 10px;")
                            div.loader.sm-loader
        div.modal.fade#contractModal(tabindex="-1" role="dialog" aria-hidden="true")
            div.modal-dialog(role="document")
                div.modal-content(v-if="currentContract != null")
                    div.modal-header
                        h5.modal-title {{ currentContract.id ? $t("project.Edit_contract") : $t("project.New_contract") }}
                        button(type="button" class="close" data-dismiss="modal" aria-label="Close" v-on:click="cancel_contract()")
                            span(aria-hidden="true") &times;
                    div.modal-body
                        div
                            fieldset.form-group(:class="{ 'has-error': errors.has('contract_name') }")
                                label.control-label {{ $t("project.Name") }}
                                input.form-control(v-model="currentContract.name" v-validate="'required'" v-bind:data-vv-as="$t('project.Name').toLowerCase()" name="contract_name")
                                span.help-block(v-show="errors.has('contract_name')") {{ errors.first('contract_name') }}
                            template(v-if="currentContract.file != null")
                                a(:href="currentContract.file" target="_blank") {{ currentContract.file_name }}
                                a.ml-2(href="#", @click.prevent="remove_contract_file")
                                    i.fa.fa-times
                            template(v-else)
                                vue-core-image-upload(
                                    v-show="!contract_file_loading",
                                    :class="{'btn': true, 'btn-diga': true}",
                                    @imageuploading="contract_file_uploading",
                                    @imageuploaded="contract_file_uploaded",
                                    @errorhandle="uploading_error",
                                    :extensions="'*'",
                                    :inputAccept="'*'",
                                    :max-file-size="$root.max_file_size",
                                    :text="$t('estimate.Upload_document')",
                                    :headers="{Authorization: $root.access_token}",
                                    url="/api/file_upload")
                                div(v-show="contract_file_loading")
                                    div.loader.sm-loader
                            fieldset.form-group(:class="{ 'has-error': errors.has('file') }")
                                input(style="display:none;" v-model="currentContract.file" v-validate="'required'" v-bind:data-vv-as="$t('project.Document').toLowerCase()" name="file")
                                div.help-block(v-show="errors.has('file')") {{ errors.first('file') }}
                    div.modal-footer(style="justify-content: space-between;")
                        button.btn.grey(v-on:click="cancel_contract()") {{ $t("template.Cancel") }}
                        button.btn.btn-diga.float-right(v-show="!loading" v-on:click="save_contract()") {{ $t("template.Save") }}
                        div(v-show="loading" style="float: right;margin-top: 10px;margin-right: 10px;")
                            div.loader.sm-loader
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['id'],
    data: function(){
        return {
            legal_entity: null,
            tmp_legal_entity: null,
            tmpContract: null,
            currentContract: null,
            contract_file_loading: false,
            loading: false,
            sign_file_loading: false,
            ready_notification_template_loading: false,
            commissioning_certificate_template_loading: false,
            commissioning_experience_certificate_template_loading: false,
        }
    },
    created() {
        this.load_legal_entity();
    },
    computed: {
        ...mapGetters({
            // unitsById: 'getEstimateUnitsById'
        }),
        attr_chunk_count(){
            return Math.ceil(this.all_attributes.length / 3);
        },
        all_attributes() {
            let attrs = [];

            attrs.push({'name': this.$root.$t('project.Name'), 'value': this.legal_entity.name});
            attrs.push({'name': this.$root.$t('project.Address'), 'value': this.legal_entity.address});
            attrs.push({'name': this.$root.$t('project.Bank_account_number'), 'value': this.legal_entity.bank_account_number});
            attrs.push({'name': this.$root.$t("project.Tax_number"), 'value': this.legal_entity.tax_number});
            attrs.push({'name': this.$root.$t("project.Kpp"), 'value': this.legal_entity.kpp_number});
            attrs.push({'name': this.$root.$t("project.Bank_name"), 'value': this.legal_entity.bank_name});
            attrs.push({'name': this.$root.$t("project.Bic"), 'value': this.legal_entity.bic});
            attrs.push({'name': this.$root.$t("project.Bank_receiver_number"), 'value': this.legal_entity.bank_receiver_number});

            return attrs;
        },
    },
    methods: {
        show_templates(){
            this.tmp_legal_entity = Object.assign({}, this.legal_entity);
            jQuery('#legalEntityModal').modal('show');
        },
        save_templates(){
            this.$http.patch('/api/legal_entities/' + this.id, this.tmp_legal_entity).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                } else {
                    this.$toastr.s(this.$root.$t("project.Project_saved"), this.$root.$t("template.Success"));
                    this.legal_entity = Object.assign({}, this.tmp_legal_entity);
                    jQuery('#legalEntityModal').modal('hide');
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        cancel_templates(){
            jQuery('#legalEntityModal').modal('hide');
        },
        contract_file_uploading(){
            this.contract_file_loading = true;
        },
        contract_file_uploaded(res){
            this.contract_file_loading = false;
            if (res.errcode == 0) {
                this.currentContract.file = res.url;
                this.currentContract.file_name = res.name;
            } else {
                this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
            }
        },
        remove_contract_file(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.currentContract.file = null;
                this.currentContract.file_name = null;
            }
        },
        new_contract(){
            let newContract = {
                id: 0,
                name: '',
                file: null,
                file_name: null,
                legal_entity_id: this.legal_entity.id,
            };
            this.currentContract = newContract;
            jQuery('#contractModal').modal('show');
        },
        edit_contract(contract){
            this.currentContract = JSON.parse(JSON.stringify(contract));
            this.tmpContract = contract;
            jQuery('#contractModal').modal('show');
        },
        cancel_contract(){
            this.currentContract = null;
            jQuery('#contractModal').modal('hide');
        },
        save_contract(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.loading = true;
                let payload = Object.assign({}, this.currentContract);
                if (this.currentContract.id == 0) {
                    this.$http.post('/api/legal_entity_contracts', payload).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Contract_saved"), this.$root.$t("template.Success"));
                            this.currentContract.id = res.data.id;
                            this.legal_entity.contracts.push(this.currentContract);
                            this.currentContract = null;
                            jQuery('#contractModal').modal('hide');
                        }
                        this.loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.loading = false;
                    });
                } else {
                    this.$http.patch('/api/legal_entity_contracts/' + this.currentContract.id, payload).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Contract_saved"), this.$root.$t("template.Success"));
                            Object.assign(this.tmpContract, this.currentContract);
                            jQuery('#contractModal').modal('hide');
                        }
                        this.loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.loading = false;
                    });
                }
            });
        },
        load_legal_entity(){
            this.$root.global_loading = true;
            this.$http.get('/api/legal_entities/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.legal_entity = res.data;
                    document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Legal_entity') + ': ' + this.legal_entity.name;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        ready_notification_template_uploading(){
            this.ready_notification_template_loading = true;
        },
        ready_notification_template_uploaded(res){
            this.ready_notification_template_loading = false;
            if (res.errcode == 0) {
                this.tmp_legal_entity.ready_notification_template_file_path = res.url;
                this.tmp_legal_entity.ready_notification_template_file_name = res.name;
            } else {
                this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
            }
        },
        remove_ready_notification(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.tmp_legal_entity.ready_notification_template_file_path = null;
                this.tmp_legal_entity.ready_notification_template_file_name = null;
            }
        },
        commissioning_experience_certificate_template_uploading(){
            this.commissioning_experience_certificate_template_loading = true;
        },
        commissioning_experience_certificate_template_uploaded(res){
            this.commissioning_experience_certificate_template_loading = false;
            if (res.errcode == 0) {
                this.tmp_legal_entity.commissioning_experience_certificate_template_file_path = res.url;
                this.tmp_legal_entity.commissioning_experience_certificate_template_file_name = res.name;
            } else {
                this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
            }
        },
        remove_commissioning_experience_certificate(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.tmp_legal_entity.commissioning_experience_certificate_template_file_path = null;
                this.tmp_legal_entity.commissioning_experience_certificate_template_file_name = null;
            }
        },
        commissioning_certificate_template_uploading(){
            this.commissioning_certificate_template_loading = true;
        },
        commissioning_certificate_template_uploaded(res){
            this.commissioning_certificate_template_loading = false;
            if (res.errcode == 0) {
                this.tmp_legal_entity.commissioning_certificate_template_file_path = res.url;
                this.tmp_legal_entity.commissioning_certificate_template_file_name = res.name;
            } else {
                this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
            }
        },
        remove_commissioning_certificate(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.tmp_legal_entity.commissioning_certificate_template_file_path = null;
                this.tmp_legal_entity.commissioning_certificate_template_file_name = null;
            }
        },
        sign_file_uploading(){
            this.sign_file_loading = true;
        },
        sign_file_uploaded(res){
            this.sign_file_loading = false;
            if (res.errcode == 0) {
                this.tmp_legal_entity.sign_file_path = res.url;
                this.tmp_legal_entity.sign_file_name = res.name;
            } else {
                this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
            }
        },
        remove_sign_file(){
            if (confirm(this.$root.$t("estimate.Are_you_sure_you_want_to_delete_the_sign"))) {
                this.tmp_legal_entity.sign_file_path = null;
                this.tmp_legal_entity.sign_file_name = null;
            }
        },
        uploading_error(e){
            this.$toastr.e(e, this.$root.$t("template.Error"));
        },
    },
}
</script>