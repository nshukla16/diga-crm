<template lang="pug">
    div(v-if="carrier")
        section.diga-container.p-3.mb-3
            .info-top
                div.caption.float-left
                    span \#{{ carrier.id }}
                    router-link.contracts-list.light-link.active.ml-2(:to="{ name: 'carrier_show', params: {id: carrier.id }}") {{ carrier.name }}
                div.float-right(style="font-size: 18pt;")
                    router-link.btn.btn-circle.btn-default-1(:to="{ name: 'carrier_edit', params: {id: carrier.id }}")
                        i.fa.fa-pencil
                div.clearfix
            .info-bottom
                .row
                    section.col-12.col-md-4
                        div.d-flex
                            span.text-muted {{ $t('project.Name') }}
                            span.dotter
                            span.text-right {{ carrier.name }}
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
                                div(v-if="carrier.contracts.length > 0")
                                    .item.with-gradient(v-for="contract in carrier.contracts")
                                        a.float-right(:href="contract.file" target="_blank") {{ contract.file_name }}
                                        span.clickable.clickable-link(v-on:click="edit_contract(contract)") {{ contract.name }}
                                div.empty-filler(v-else) {{ $t('project.No_contracts') }}
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
    data: function () {
        return {
            carrier: null,
            tmpContract: null,
            currentContract: null,
            contract_file_loading: false,
            loading: false,
        }
    },
    created() {
        this.load_carrier();
    },
    methods: {
        new_contract() {
            let newContract = {
                id: 0,
                name: '',
                file: null,
                file_name: null,
                carrier_id: this.carrier.id,
            };
            this.currentContract = newContract;
            jQuery('#contractModal').modal('show');
        },
        edit_contract(contract) {
            this.currentContract = JSON.parse(JSON.stringify(contract));
            this.tmpContract = contract;
            jQuery('#contractModal').modal('show');
        },
        cancel_contract() {
            this.currentContract = null;
            jQuery('#contractModal').modal('hide');
        },
        save_contract() {
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.loading = true;
                let payload = Object.assign({}, this.currentContract);
                if (this.currentContract.id == 0) {
                    this.$http.post('/api/carrier_contracts', payload).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Contract_saved"), this.$root.$t("template.Success"));
                            this.currentContract.id = res.data.id;
                            this.carrier.contracts.push(this.currentContract);
                            this.currentContract = null;
                            jQuery('#contractModal').modal('hide');
                        }
                        this.loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.loading = false;
                    });
                } else {
                    this.$http.patch('/api/carrier_contracts/' + this.currentContract.id, payload).then(res => {
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
        load_carrier(){
            this.$root.global_loading = true;
            this.$http.get('/api/carriers/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.carrier = res.data;
                    document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Carrier') + ': ' + this.carrier.name;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        uploading_error(e){
            this.$toastr.e(e, this.$root.$t("template.Error"));
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
    },
}
</script>