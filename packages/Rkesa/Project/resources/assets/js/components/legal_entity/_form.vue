<style>

</style>

<template lang="pug">
    section.diga-container.p-4(v-if="legal_entity")
        h2 {{ isCreating ? $t('project.New_entity') : $t('project.Edit_entity') }}
        .row
            section.col-12.col-md-4
                fieldset.form-group(:class="{ 'has-error': errors.has('name') }")
                    label {{ $t('project.Name') }}
                    input.form-control(name="name", v-validate="'required'", type="text", v-model="legal_entity.name" v-bind:data-vv-as="$t('project.Name').toLowerCase()")
                    h6.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
                fieldset.form-group
                    label {{ $t("project.Address") }}:
                    input.form-control(v-model="legal_entity.address")
                fieldset.form-group
                    label.control-label {{ $t("project.Tax_number") }}
                    input.form-control(v-model="legal_entity.tax_number")
            section.col-12.col-md-4
                fieldset.form-group
                    label.control-label {{ $t("project.Kpp") }}
                    input.form-control(v-model="legal_entity.kpp_number")
                fieldset.form-group
                    label.control-label {{ $t("project.Bank_name") }}
                    input.form-control(v-model="legal_entity.bank_name")
                fieldset.form-group
                    label.control-label {{ $t("project.Bic") }}
                    input.form-control(v-model="legal_entity.bic")
            section.col-12.col-md-4
                fieldset.form-group
                    label.control-label {{ $t("project.Bank_receiver_number") }}
                    input.form-control(v-model="legal_entity.bank_receiver_number")
                fieldset.form-group
                    label.control-label {{ $t("project.Bank_account_number") }}
                    input.form-control(v-model="legal_entity.bank_account_number")
        .row
            div.col-12
                h3 {{ $t('project.System_info') }}
            section.col-12.col-md-4
                fieldset.form-group
                    label.control-label {{ $t("project.Last_manufacturer_order_number") }}
                    input.form-control(v-model="legal_entity.last_logistic_order_number")
                fieldset.form-group
                    label.control-label {{ $t("project.Manufacturer_order_number_format") }}
                    input.form-control(v-model="legal_entity.logistic_order_number_format")
        button.btn.btn-diga(style="margin: 10px 0 0 0" v-on:click="store_entity()") {{ $t("project.Save") }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['id'],
    data(){
        return {
            legal_entity: null,
            isCreating: true,
        }
    },
    computed: {
        ...mapGetters({
            // legal_entities: 'getLegalEntities',
            // project_types: 'getProjectTypes'
        }),
    },
    mounted(){
        if (this.id) {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Edit_entity');
            this.load_legal_entity();
        } else {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.New_entity');
            let newLegalEntity = {
                name: this.$root.$t('project.New_entity'),
                address: '',
                bank_account_number: '',
                tax_number: '',
                kpp_number: '',
                bank_name: '',
                bic: '',
                bank_receiver_number: '',
                last_logistic_order_number: 0,
                logistic_order_number_format: '{%n}',
            };
            this.legal_entity = Object.assign({}, newLegalEntity);
        }
    },
    methods: {
        load_legal_entity(){
            this.$root.global_loading = true;
            this.$http.get('/api/legal_entities/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.legal_entity = res.data;
                    this.isCreating = false;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        store_entity(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.$root.global_loading = true;
                if (this.isCreating) {
                    this.$http.post('/api/legal_entities', this.legal_entity).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                            this.$root.global_loading = false;
                        } else {
                            this.$toastr.s(this.$root.$t("project.Legal_entity_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'legal_entity_show', params: {id: res.data.id}});
                        }
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                } else {
                    this.$http.patch('/api/legal_entities/' + this.id, this.legal_entity).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                            this.$root.global_loading = false;
                        } else {
                            this.$toastr.s(this.$root.$t("project.Project_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'legal_entity_show', params: {id: this.id}});
                        }
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