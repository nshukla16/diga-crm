<template lang="pug">
    section.diga-container.p-4
        h2 {{ isCreating ? $t('project.Specification_new') : $t('project.Specification_edit') }}
        .row(v-if="currentSpecification")
            section.col-12.col-md-3
                fieldset.form-group(:class="{ 'has-error': errors.has('name') }")
                    label {{ $t('project.Name') }}
                    input.form-control(name="name", v-validate="'required'", type="text", v-model="currentSpecification.name" v-bind:data-vv-as="$t('project.Name').toLowerCase()")
                    h6.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
                fieldset.form-group
                    label {{ $t('project.Notes') }}
                    textarea.form-control(v-model="currentSpecification.notes")
            section.col-12.col-md-9
                equipment-table(:spec="currentSpecification", :selectable="true", :disabled="false")
        button.btn.btn-diga(v-on:click="save") {{ $t('template.Save') }}
</template>

<script>
import equipment_table from './../shared/equipment_table.vue'

export default {
    components: {
        'equipment-table': equipment_table,
    },
    props: ['id'],
    data(){
        return {
            currentSpecification: null,
            isCreating: true,
        }
    },
    created(){
        if (this.id) {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Specification_edit');
            this.load_specification();
        } else {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Specification_new');
            let newSpecification = {
                id: 0,
                name: 'Specification',
                equipment: [],
                removed_equipment: [],
            };
            this.currentSpecification = Object.assign({}, newSpecification);
        }
    },
    methods: {
        load_specification(){
            this.$root.global_loading = true;
            this.$http.get('/api/specifications/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.currentSpecification = res.data;
                    this.currentSpecification.removed_equipment = [];
                    this.isCreating = false;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        save(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.$root.global_loading = true;
                if (this.isCreating) {
                    this.$http.post('/api/specifications/', this.currentSpecification).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("client.Client_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'specifications_index'});
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                } else {
                    this.$http.patch('/api/specifications/' + this.id, this.currentSpecification).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("client.Client_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'specifications_index'});
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