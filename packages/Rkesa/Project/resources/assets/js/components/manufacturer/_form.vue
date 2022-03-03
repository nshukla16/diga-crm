<template lang="pug">
    section.diga-container.p-4
        h2 {{ isCreating ? $t('project.Manufacturer_new') : $t('project.Manufacturer_edit') }}
        .row(v-if="currentManufacturer")
            section.col-12.col-md-4
                fieldset.form-group(:class="{ 'has-error': errors.has('name') }")
                    label {{ $t('project.Name') }}
                    input.form-control(name="name", v-validate="'required'", type="text", v-model="currentManufacturer.name", v-bind:data-vv-as="$t('project.Name').toLowerCase()")
                    h6.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
            section.col-12.col-md-4
                fieldset.form-group
                    label {{ $t('project.Legal_address') }}
                    input.form-control(type="text", v-model="currentManufacturer.legal_address")
            section.col-12.col-md-4
                fieldset.form-group
                    label {{ $t('project.Uploading_address') }}
                    input.form-control(type="text", v-model="currentManufacturer.uploading_address")
            section.col-12.col-md-4
                fieldset.form-group
                    label {{ $t('project.Bank_name') }}
                    input.form-control(type="text", v-model="currentManufacturer.bank_name")
            section.col-12.col-md-4
                fieldset.form-group
                    label {{ $t('client.Bic') }}
                    input.form-control(type="text", v-model="currentManufacturer.bic")
            section.col-12.col-md-4
                fieldset.form-group
                    label {{ $t('client.Checking_account') }}
                    input.form-control(type="text", v-model="currentManufacturer.checking_account")
            section.col-12.col-md-4
                fieldset.form-group
                    label {{ $t('client.Correspondent_account') }}
                    input.form-control(type="text", v-model="currentManufacturer.correspondent_account")
        button.btn.btn-diga(style="margin: 10px 0 0 0" v-on:click="store_manufacturer()") {{ $t("project.Save") }}
</template>

<script>

export default {
    props: ['id'],
    data(){
        return {
            currentManufacturer: null,
            isCreating: true,
        }
    },
    created(){
        if (this.id) {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Manufacturer_edit');
            this.load_manufacturer();
        } else {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Manufacturer_new');
            let newManufacturer = {
                name: '',
                legal_address: '',
                uploading_address: '',
            };
            this.currentManufacturer = Object.assign({}, newManufacturer);
        }
    },
    methods: {
        load_manufacturer(){
            this.$root.global_loading = true;
            this.$http.get('/api/manufacturers/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.currentManufacturer = res.data;
                    this.isCreating = false;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        store_manufacturer(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.$root.global_loading = true;
                if (this.isCreating) {
                    this.$http.post('/api/manufacturers', this.currentManufacturer).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Manufacturer_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'manufacturer_show', params: {id: res.data.id}});
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                } else {
                    this.$http.patch('/api/manufacturers/' + this.id, this.currentManufacturer).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Manufacturer_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'manufacturer_show', params: {id: this.id}});
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                }
            });
        },
    },
}
</script>