<template lang="pug">
div
    div
        section.diga-container(style="height: 550px;")
            .portlet-title
                .caption(style="padding-right: 10px;")
                    a.btn(v-on:click="new_order()")
                        i.fa.fa-plus
                    span.caption-subject.bold.uppercase {{ $t("project.Orders") }}
            .portlet-body
                div(v-bar="" style="height: 480px;")
                    div
                        div(v-if="manufacturer.orders.length > 0")
                            .item.with-gradient(v-for="order in manufacturer.orders")
                                div
                                    b(style="font-size: 20px;") {{ order.name }}
                                    div.float-right
                                        span.clickable.clickable-link(v-on:click="edit_order(order)")
                                            i.fa.fa-pencil
                                div
                                    b.mr-2 {{ $t("project.Client") }}:
                                    router-link(:to="{name: 'company_show', params: {id: order.client_option.id}}") {{ order.client_option.name }}
                                div(v-if="order.contract_number")
                                    b.mr-2 {{ $t("project.Contract_number") }}:
                                    | {{ order.contract_number }}
                                div(v-if="order.specification_number")
                                    b.mr-2 {{ $t("project.Specification") }}:
                                    | {{ order.specification_number }}
                                div(v-if="order.file")
                                    b.mr-2 {{ $t("project.File") }}:
                                    a(:href="order.file" target="_blank") {{ order.file_name }}
                        div.empty-filler(v-else) {{ $t('project.No_orders') }}
    div.modal.fade#orderModal(tabindex="-1" role="dialog" aria-hidden="true")
        div.modal-dialog(role="document")
            div.modal-content(v-if="currentOrder != null")
                div.modal-header
                    h5.modal-title {{ currentOrder.id ? $t("project.Edit_order") : $t("project.New_order") }}
                    button(type="button" class="close" data-dismiss="modal" aria-label="Close" v-on:click="cancel_order()")
                        span(aria-hidden="true") &times;
                div.modal-body
                    div
                        fieldset.form-group(:class="{ 'has-error': errors.has('name') }")
                            label.control-label {{ $t("project.Name") }}
                            input.form-control(v-model="currentOrder.name" v-validate="'required'" v-bind:data-vv-as="$t('project.Name').toLowerCase()" name="name")
                            span.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
                        fieldset.form-group(:class="{ 'has-error': errors.has('client_id') }")
                            label {{ $t('project.Client') }}
                            v-select(:debounce='250',
                                v-model="currentOrder.client_option",
                                v-validate="'required'",
                                v-bind:data-vv-as="$t('project.Client').toLowerCase()",
                                :on-search='get_companies_options',
                                :on-change='company_selected',
                                :options='tmp_companies',
                                label="name",
                                :placeholder="$t('project.Start_to_enter_company_name')",
                                name="client_id")
                                template(slot="no-options") {{ $t('template.No_matching_options') }}
                            span.help-block(v-show="errors.has('client_id')") {{ errors.first('client_id') }}
                        fieldset.form-group
                            label.control-label {{ $t("project.Contract_number") }}
                            input.form-control(v-model="currentOrder.contract_number")
                        fieldset.form-group
                            label.control-label {{ $t("project.Specification") }}
                            input.form-control(v-model="currentOrder.specification_number")
                        div
                            template(v-if="currentOrder.file != null")
                                a(:href="currentOrder.file" target="_blank") {{ currentOrder.file_name }}
                                a.ml-2(href="#", @click.prevent="remove_file")
                                    i.fa.fa-times
                            template(v-else)
                                vue-core-image-upload(
                                    v-show="!file_loading",
                                    :class="['btn', 'btn-diga']",
                                    @imageuploading="file_uploading",
                                    @imageuploaded="file_uploaded",
                                    @errorhandle="uploading_error",
                                    :extensions="'*'",
                                    :inputAccept="'*'",
                                    :max-file-size="$root.max_file_size",
                                    :text="$t('estimate.Upload_document')",
                                    :headers="{Authorization: $root.access_token}",
                                    url="/api/file_upload")
                                div(v-show="file_loading")
                                    div.loader.sm-loader
                div.modal-footer(style="justify-content: space-between;")
                    button.btn.grey(v-on:click="cancel_order()") {{ $t("template.Cancel") }}
                    button.btn.btn-diga.float-right(v-show="!loading" v-on:click="save_order()") {{ $t("template.Save") }}
                    div(v-show="loading" style="float: right;margin-top: 10px;margin-right: 10px;")
                        div.loader.sm-loader
</template>

<script>
    export default {
        props: ['manufacturer'],
        data: function() {
            return {
                tmpOrder: null,
                currentOrder: null,
                tmp_companies: [],
                file_loading: false,
                loading: false,
            }
        },
        methods:{
            uploading_error(e){
                this.$toastr.e(e, this.$root.$t("template.Error"));
            },
            file_uploading(){
                this.file_loading = true;
            },
            file_uploaded(res){
                this.file_loading = false;
                if (res.errcode == 0) {
                    this.currentOrder.file = res.url;
                    this.currentOrder.file_name = res.name;
                } else {
                    this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
                }
            },
            remove_file(){
                if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                    this.currentOrder.file = null;
                    this.currentOrder.file_name = null;
                }
            },
            get_companies_options(search, loading){
                loading(true);
                this.$http.get('/api/companies?query=' + search + '&limit=100&fields=id,name').then(res => {
                    this.tmp_companies = res.data.rows;
                    loading(false);
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                })
            },
            company_selected(val){
                if (val !== null){
                    this.currentOrder.client_option = val;
                    this.currentOrder.client_id = val.id;
                } else {
                    this.currentOrder.client_option = null;
                    this.currentOrder.client_id = null;
                }
            },
            new_order(){
                let newOrder = {
                    id: 0,
                    manufacturer_id: this.manufacturer.id,
                    name: '',
                    client_id: null,
                    contract_number: '',
                    specification_number: '',
                    file: null,
                    file_name: null,
                    client_option: null,
                };
                this.currentOrder = newOrder;
                jQuery('#orderModal').modal('show');
            },
            edit_order(order){
                this.currentOrder = JSON.parse(JSON.stringify(order));
                this.tmpOrder = order;
                jQuery('#orderModal').modal('show');
            },
            cancel_order(){
                this.currentOrder = null;
                jQuery('#orderModal').modal('hide');
            },
            save_order(){
                this.$validator.validateAll().then(result => {
                    if (!result) {
                        this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                        return;
                    }
                    this.loading = true;
                    let payload = Object.assign({}, this.currentOrder);
                    if (this.currentOrder.id == 0) {
                        this.$http.post('/api/manufacturer_actual_orders', payload).then(res => {
                            if (res.data.errcode == 1) {
                                this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                            } else {
                                this.$toastr.s(this.$root.$t("project.Order_saved"), this.$root.$t("template.Success"));
                                this.currentOrder.id = res.data.id;
                                this.manufacturer.orders.push(this.currentOrder);
                                this.currentOrder = null;
                                jQuery('#orderModal').modal('hide');
                            }
                            this.loading = false;
                        }, res => {
                            this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                            this.loading = false;
                        });
                    } else {
                        this.$http.patch('/api/manufacturer_actual_orders/' + this.currentOrder.id, payload).then(res => {
                            if (res.data.errcode == 1) {
                                this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                            } else {
                                this.$toastr.s(this.$root.$t("project.Order_saved"), this.$root.$t("template.Success"));
                                Object.assign(this.tmpOrder, this.currentOrder);
                                jQuery('#orderModal').modal('hide');
                            }
                            this.loading = false;
                        }, res => {
                            this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                            this.loading = false;
                        });
                    }
                });
            },
        }
    }
</script>
