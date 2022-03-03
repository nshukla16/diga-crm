<style>

</style>

<template lang="pug">
    section.diga-container.p-4(v-if="project")
        project_changed_window(:project_id="id")
        h2 {{ isCreating ? $t('project.Project_new') : $t('project.Project_edit') }}
        general(:project="project")
        button.btn.btn-diga(style="margin: 10px 0 0 0" v-on:click="store_project()") {{ $t("project.Save") }}
</template>

<script>
import general from './general.vue';
import {mapGetters} from "vuex";
import project_changed_window from './project_changed_window.vue';

export default {
    props: ['id'],
    components: {
        general, project_changed_window,
    },
    data(){
        return {
            project: null,
            isCreating: true,
        }
    },
    computed: {
        ...mapGetters({
            legal_entities: 'getLegalEntities',
            project_types: 'getProjectTypes',
        }),
    },
    mounted(){
        if (this.id) {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Project_edit');
            this.load_project();
        } else {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Project_new');
            let newProject = {
                id: 0,
                name: this.$root.$t('project.Project_new'),
                project_type_id: this.project_types[0].id,
                contract_file: null,
                contract_filename: null,
                specification_file: null,
                specification_filename: null,
                specifications: [
                    {manufacturer_id: 0},
                ],
                manufacturers: [],
                contract_payments: [],
                client_id: null,
                selected_company_object: null,
                selected_service_object: null,
                service_id: null,
                responsible_user_id: this.$root.user.id,
                lessee_client_id: null,
                seller_legal_entity_id: this.legal_entities[0].id,
                // commissioner_legal_entity_id: this.legal_entities[0].id,
                contract_currency: this.$root.global_settings.currency,
                contract_currency_type: 1,
                contract_type: 0,
                phased_deliveries: 0,
                conditions_of_delivery: 0,
                limit_type: 0,
                limit_forming_type: 0,
                limit_forming_date: 0,
                limit_forming_days: 0,
                ready_notification_file_path: null,
                ready_notification_file_name: null,
                acceptance_certificate_file_path: null,
                acceptance_certificate_file_name: null,
                shipping_documents_sent_file_path: null,
                shipping_documents_sent_file_name: null,
                shipping_documents_received_file_path: null,
                shipping_documents_received_file_name: null,
                contract_total_price: 0,
                logistics_enabled: true,
            };
            this.project = Object.assign({}, newProject);
        }
    },
    methods: {
        load_project(){
            this.$root.global_loading = true;
            this.$http.get('/api/projects/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.project = res.data;
                    this.isCreating = false;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        store_project(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                if (this.project.contract_type == 1 && this.project.commissioners.length == 0){
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    this.errors.add('no_commissioners', this.$root.$t("project.No_commissioners"));
                    return;
                }
                if (this.project.manufacturers.length == 0){
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    this.errors.add('no_manufacturers', this.$root.$t("project.No_manufacturers"));
                    return;
                }
                this.$root.global_loading = true;
                if (this.isCreating) {
                    this.project.logistics_enabled = this.project.contract_type == 0;
                    this.$http.post('/api/projects', this.project).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                            this.$root.global_loading = false;
                        } else {
                            this.$toastr.s(this.$root.$t("project.Project_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'project_show', params: {id: res.data.id}});
                        }
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                } else {
                    this.$http.patch('/api/projects/' + this.id, this.project).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                            this.$root.global_loading = false;
                        } else {
                            this.$toastr.s(this.$root.$t("project.Project_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'project_show', params: {id: this.id}});
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