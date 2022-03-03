<style>

</style>

<template lang="pug">
//- div
//-     div.modal.fade(:id="'modal-access_' + this.service.id" tabindex="-1" aria-hidden="true")
//-         div.modal-dialog.modal-dialog-centered(role="document")
//-             div.modal-content
//-                 div.modal-header
//-                     h5.modal-title {{ $t("client.Access") }}
//-                 div.modal-body
div
    section.diga-container.p-4
        h2 {{ $t("client.Access") }} (Telegram)
        div.form(v-if="service")
            div.form-group.row
                label.col-sm-3.col-form-label {{$t('client.Estimate')}}
                div.col-sm-3
                    input.form-control(disabled="disabled" :value="service.estimate_number")
        div(v-if="service")
            div.mb-2.d-flex
                div(style="flex: 1;line-height: 36px;") {{ $t('client.Enable_access') }}
                div(v-on:click="enable_access")
                    bootstrap-toggle(v-model="service.access_enabled", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default")
            div(v-if="service.access_enabled")
                div.mb-2.d-flex
                    div(style="flex: 1;line-height: 36px;") {{ $t('client.Access_link') }}
                    i.fa.fa-clipboard.clickable.hoverable.mr-2(style="line-height: 36px;" @click="copy_link_to_clipboard")
                    input.form-control(id="access_link" style="flex:1;" :value="format_link(service.access_token)")
                fieldset.form-group
                    a(href="#" @click.prevent="generate_new_link" style="float:right;") {{ $t('client.Generate_new_access_link') }}
            div(v-if="service.access_enabled" style="margin-top: 20px; padding-bottom: 20px;")
                label.typo__label {{$t('client.select_responsible_users')}}
                multiselect(
                    v-model="responsibles",
                    :options="users.filter(u => u.tg_id > 0)",
                    :multiple="true",
                    :close-on-select="false",
                    :clear-on-select="false",
                    :preserve-search="true",
                    :placeholder="$t('client.select_responsible_users')"
                    label="name",
                    track-by="name",
                    :selectLabel="$t('dashboard.press_enter_to_select')",
                    :deselectLabel="$t('dashboard.press_enter_to_remove')",
                    :selectedLabel="$t('dashboard.option_selected')",
                )
            button.btn.btn-diga(v-on:click="save") {{$t('template.Save')}}
        div.text-center(style="margin-top: 10px;")
            router-link.btn.btn-diga(v-if='service && service.client_contact_id', :to="{name: this.$root.contact_or_client_show_route(), params: {id: service.client_contact_id }}") {{ $t('estimate.Open_client_card') }}

</template>

<script>
import moment from 'moment'
import process from './process.vue'

import { mapGetters } from 'vuex';

export default {
    data() {
        return {
            service: null,
            responsibles: []
        }
    },
    props: ['id'],
    mounted() {
        this.responsibles = [];
        this.getService();
    },
    methods: {
        getService() {
            this.$http.get('/api/services/' + this.id).then(res => {
                this.service = res.data;
                this.responsibles = [];
                if (this.service.access_enabled == true){
                    this.get_members();
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
        copy_link_to_clipboard(){
            let testingCodeToCopy = document.querySelector('#access_link');
            testingCodeToCopy.setAttribute('type', 'text');
            testingCodeToCopy.select();
        
            try {
                let successful = document.execCommand('copy');
                if (!successful){
                    alert('Oops, unable to copy');
                }
            } catch (err) {
                alert('Oops, unable to copy');
            }
        },
        enable_access(){
            this.$root.global_loading = true;
            this.$http.post('/api/services/' + this.service.id + '/enable_access').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.service.access_token = res.data.link;
                    if (this.service.access_enabled == false){
                        this.responsibles = [];
                    }
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        generate_new_link(){
            this.$root.global_loading = true;
            this.$http.post('/api/services/' + this.service.id + '/generate_new_link').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.service.access_token = res.data.link;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        format_link(token){
            return window.location.host + '/access/' + token;
        },
        get_members(){
            this.$root.global_loading = true;
            this.$http.get('/api/services/' + this.service.id + '/get_members').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    let members = res.data.members;
                    if (members){
                        members.forEach(m => {
                            if (m.user_id){
                                this.responsibles.push(this.users_by_id[m.user_id]);
                            }                        
                        });
                    }
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });           
        },
        save(){
            this.$root.global_loading = true;
            this.$http.post('/api/services/' + this.service.id + '/save_access', {'responsibles': this.responsibles}).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    jQuery('#modal-access_' + this.service.id).modal('hide');
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        }
    },
    computed: {
        ...mapGetters({
            users: 'getNotRemovedUsers',
            users_by_id: 'getUsersById',
        }),
    },
}
</script>