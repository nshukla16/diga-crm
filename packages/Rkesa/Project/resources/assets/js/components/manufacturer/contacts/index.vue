<template lang="pug">
div
    div
        section.diga-container
            .portlet-title
                .caption(style="padding-right: 10px;")
                    a.btn(v-on:click="new_contact()")
                        i.fa.fa-plus
                    span.caption-subject.bold.uppercase {{ $t("project.Contacts") }}
            .portlet-body
                table.table.table-striped
                    thead
                        tr
                            td {{ $t('project.Contact_name') }}
                            td {{ $t('project.Phone') }}
                            td {{ $t('project.Email') }}
                            td
                    tbody
                        template(v-if="manufacturer.contacts.length > 0")
                            tr(v-for="contact in manufacturer.contacts")
                                td {{ contact.name }}
                                td {{ contact.phone }}
                                td {{ contact.email }}
                                td.column_autosize
                                    button.btn(v-on:click="edit_contact(contact)") {{ $t('template.Edit') }}
                        template(v-else)
                            tr
                                td(colspan="4")
                                    div.empty-filler {{ $t('project.No_contacts') }}
    div.modal.fade#contactModal(tabindex="-1" role="dialog" aria-hidden="true")
        div.modal-dialog(role="document")
            div.modal-content(v-if="currentContact != null")
                div.modal-header
                    h5.modal-title {{ currentContact.id ? $t("project.Edit_contact") : $t("project.New_contact") }}
                    button(type="button" class="close" data-dismiss="modal" aria-label="Close" v-on:click="cancel_contact()")
                        span(aria-hidden="true") &times;
                div.modal-body
                    div
                        fieldset.form-group(:class="{ 'has-error': errors.has('contact_name') }")
                            label.control-label {{ $t("project.Contact_name") }}
                            input.form-control(v-model="currentContact.name" v-validate="'required'" v-bind:data-vv-as="$t('project.Contact_name').toLowerCase()" name="contact_name")
                            span.help-block(v-show="errors.has('contact_name')") {{ errors.first('contact_name') }}
                        fieldset.form-group
                            label.control-label {{ $t("project.Phone") }}
                            input.form-control(v-model="currentContact.phone")
                        fieldset.form-group
                            label.control-label {{ $t("project.Email") }}
                            input.form-control(v-model="currentContact.email")
                div.modal-footer(style="justify-content: space-between;")
                    button.btn.grey(v-on:click="cancel_contact()") {{ $t("template.Cancel") }}
                    button.btn.btn-diga.float-right(v-show="!loading" v-on:click="save_contact()") {{ $t("template.Save") }}
                    div(v-show="loading" style="float: right;margin-top: 10px;margin-right: 10px;")
                        div.loader.sm-loader
</template>

<script>

export default {
    props: ['manufacturer'],
    components: {
    },
    data: function () {
        return {
            tmpContact: null,
            currentContact: null,
            loading: false,
        }
    },
    methods: {
        new_contact(){
            let newContact = {
                id: 0,
                name: '',
                phone: '',
                manufacturer_id: this.manufacturer.id,
                email: '',
            };
            this.currentContact = newContact;
            jQuery('#contactModal').modal('show');
        },
        edit_contact(contact){
            this.currentContact = JSON.parse(JSON.stringify(contact));
            this.tmpContact = contact;
            jQuery('#contactModal').modal('show');
        },
        cancel_contact(){
            this.currentContact = null;
            jQuery('#contactModal').modal('hide');
        },
        save_contact(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.loading = true;
                let payload = Object.assign({}, this.currentContact);
                if (this.currentContact.id == 0) {
                    this.$http.post('/api/manufacturer_contacts', payload).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Contact_saved"), this.$root.$t("template.Success"));
                            this.currentContact.id = res.data.id;
                            this.manufacturer.contacts.push(this.currentContact);
                            this.currentContact = null;
                            jQuery('#contactModal').modal('hide');
                        }
                        this.loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.loading = false;
                    });
                } else {
                    this.$http.patch('/api/manufacturer_contacts/' + this.currentContact.id, payload).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Contact_saved"), this.$root.$t("template.Success"));
                            Object.assign(this.tmpContact, this.currentContact);
                            jQuery('#contactModal').modal('hide');
                        }
                        this.loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.loading = false;
                    });
                }
            });
        },
    },
    computed:{
    }

}
</script>