<style>
    .estimates_list .modal-dialog{
        max-width: 980px;
    }
</style>

<template lang="pug">
    div.modal.fade.estimates_list(tabindex="-1" aria-hidden="true")
        div.modal-dialog.modal-dialog-centered(role="document")
            div.modal-content
                div.modal-body(v-if="service")
                    div.table-responsive(v-if="'estimates' in service && service.estimates.length > 0")
                        table.estimates.table.table-striped.table-hover
                            thead
                                tr
                                    td.text-center(style="width: 10%;font-size: 20px;") {{ $t('client.Master') }}
                                    td.text-center(style="width: 15%;font-size: 20px;") {{ $t('client.Number') }}
                                    td.text-center(style="width: 45%;font-size: 20px;") {{ $t('client.Estimates') }}
                                    td.text-center(v-if="$root.can_do('plannings', 'update') != 0 || $root.can_do('plannings', 'read') != 0" style="width: 20%;font-size: 20px;") {{ $t('client.Plannings') }}
                                    td.text-center(style="width: 10%;font-size: 20px;") {{ $t('client.Responsible') }}
                            tbody
                                tr(v-for="estimate in service.estimates" :key="estimate.id")
                                    td.text-center
                                        input(type="radio" v-model="service.master_estimate_id" v-bind:value="estimate.id" v-on:click="change_master(estimate.id)")
                                    td.text-center
                                        | {{ $root.estimate_number(estimate) }}
                                    td.text-center
                                        div
                                            i.fa.fa-lock.estimate-locker(v-if="estimate.blocked" v-on:click="block_unblock(estimate)")
                                            i.fa.fa-unlock.estimate-locker(v-else v-on:click="block_unblock(estimate)")
                                            button.btn.default.mr-2(v-on:click="open_edit_estimate_page(estimate.id)" v-if="!estimate.blocked && $root.can_with_estimate('update', estimate)") {{ $t("template.Edit") }}
                                            router-link.btn.default.mr-2(:to="{name: 'estimate_show', params: {id: estimate.id}}" target="_blank") PDF
                                            button.btn.default.mr-2(@click="export_results(estimate)") Excel
                                            template(v-if="$root.can_do('estimates', 'create') != 0")
                                                span.mr-2
                                                    i.fa.fa-chevron-right
                                                button.btn.default.mr-2(v-on:click="open_create_revision_page(estimate.id)") {{ $t('client.Revision') }}
                                                button.btn.default.mr-2(v-on:click="open_create_option_page(estimate.id)") {{ $t('client.Option') }}
                                    td.text-center(v-if="$root.can_do('plannings', 'update') != 0 || $root.can_do('plannings', 'read') != 0")
                                        button.btn.default.mr-2(v-if="$root.can_do('plannings', 'update') != 0" v-on:click="open_edit_planning_page(estimate.id)") {{ $t("template.Edit") }}
                                        router-link.btn.default(v-if="$root.can_do('plannings', 'read') != 0", :to="{name: 'planning_show', params: {id: estimate.id}}" target="_blank") PDF
                                    td.text-center {{ users_by_id[estimate.user_id].name }}
                        div.text-center            
                            button.btn.btn-diga.mt-2(v-if="$root.can_do('estimates', 'create') != 0", v-on:click="is_import = !is_import") {{ $t("template.import_estimate") }}
                    div(v-else style="text-align: center;margin: 10px 0;")
                        |  {{ $t("client.no_estimates") }}
                        br
                        button.btn.btn-diga.mt-2(v-if="$root.can_do('estimates', 'create') != 0", v-on:click="open_create_estimate_page") {{ $t("client.Create_estimate") }}
                        button.btn.btn-diga.mt-2.mx-3(v-if="$root.can_do('estimates', 'create') != 0", v-on:click="is_import = !is_import") {{ $t("template.import_estimate") }}
                    div(v-if="is_import" style="text-align: center;margin: 10px 0;")
                        div(v-html="$t('template.Import_estimate_full_desc')").mb-2
                        vue-core-image-upload(
                            :class="['btn', 'btn-diga']",
                            @imageuploading="imageuploading",
                            @imageuploaded="imageuploaded",
                            @errorhandle="imageerror",
                            :headers="{Authorization: $root.access_token}",
                            :extensions="'xlsx,xls,ods,csv'",
                            :inputAccept="'xlsx,xls,ods,csv'",
                            :max-file-size="$root.max_file_size",
                            :text="$t('estimate.Upload_document')"
                            url="/api/file_upload")
                        a.ml-3(href="/estimate_example.xlsx") {{ $t('template.Import_example') }}
                        div(v-show="loading")
                            div.loader.sm-loader
                        div.my-3(v-if="url") 
                            span
                                a(:href="url" target="_blank") {{name}}
                            span.mx-2
                                a(@click="clear") X
                        div.my-3(v-if="url")
                            button.btn.btn-diga(@click="import_estimate") {{ $t('template.Import') }}

</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['service', 'forks'],
    data() {
        return {
            is_import: false,
            loading: false,
            url: null,
            name: null,
        }
    },
    computed: {
        ...mapGetters({
            users_by_id: 'getUsersById',
        }),
    },
    methods: {
        export_results(estimate){
            this.$root.global_loading = true;
            this.$http.get('/api/estimates/export?id=' + estimate.id, {responseType: 'blob'}).then(response => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', this.$root.$t("estimate.estimate_number") + ' ' + this.$root.estimate_number(estimate) + '.xlsx');
                document.body.appendChild(link);
                link.click();
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },        
        import_estimate(){
            
            this.$root.global_loading = true;
            this.$http.post('/api/estimates/import_data', {
                src: this.url,
                filename: this.name,
                service_id: this.service.id
            }).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                } else {
                    this.$toastr.s(this.$root.$t("template.Import_complete"), this.$root.$t("template.Success"));
                    this.$root.global_loading = false;

                    this.url = null;
                    this.is_import = false;
                    this.name = null;
                    jQuery('#view_estimate_modal').modal('hide');


                    // setTimeout(function () {
                    //     this.$root.global_loading = false;
                    //     this.$bus.$emit("refetch_estimates");
                    // }.bind(this), 2000)                   
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });            
        },
        clear(){
            this.url = null;
            this.name = null;
        },
        imageuploading() {
            this.loading = true;
        },
        imageuploaded(res) {
            this.loading = false;
            if (res.errcode == 0) {
                this.url = res.url;
                this.name = res.name;
            } else {
                this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
            }
        },
        imageerror(e){
            this.loading = false;
            this.$toastr.e(e, this.$root.$t("template.Error"));
        },
        open_edit_planning_page(estimate_id){
            jQuery('#view_estimate_modal').modal('hide');
            this.$router.push({name: 'planning_edit', params: {id: estimate_id}});
        },
        open_edit_estimate_page(estimate_id){
            jQuery('#view_estimate_modal').modal('hide');
            this.$router.push({name: 'estimate_edit', params: {id: estimate_id}});
        },
        open_create_estimate_page(){
            jQuery('#view_estimate_modal').modal('hide');
            this.$router.push({name: 'estimate_create', query: {service_id: this.service.id}});
        },
        open_create_revision_page(estimate_id){
            jQuery('#view_estimate_modal').modal('hide');
            this.$router.push({name: 'estimate_create', query: {base_estimate_id: estimate_id, action: 'rev'}});
        },
        open_create_option_page(estimate_id){
            jQuery('#view_estimate_modal').modal('hide');
            this.$router.push({name: 'estimate_create', query: {base_estimate_id: estimate_id, action: 'opt'}});
        },
        change_master(estimate_id){
            this.$http.post('/api/estimates/set_master_estimate', {id: estimate_id}).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.service.estimate_summ = res.data.estimate_summ;
                    this.$toastr.s(this.$root.$t("client.Master_estimate_changed"), this.$root.$t("template.Success"));
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
        block_unblock: function(estimate){
            if (estimate.user_id == this.$root.user.id || this.$root.global_settings.unlocker_user_id == this.$root.user.id || this.$root.user.is_admin) {
                this.$http.post('/api/estimates/' + estimate.id + '/block', {blocked: !estimate.blocked}).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        estimate.blocked = !estimate.blocked;
                        this.$toastr.s(this.$root.$t("client.Estimate_state_changed"), this.$root.$t("template.Success"));
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            } else {
                this.$toastr.e(this.$root.$t("client.Only_owner_can_change_block_state"), this.$root.$t("template.Error"));
            }
        },
    },
}
</script>