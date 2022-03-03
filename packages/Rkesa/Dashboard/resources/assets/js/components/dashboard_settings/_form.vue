<style>
    .add-widget:hover{
        background-color: #eee;
    }
</style>

<template lang="pug">
    section
        div.diga-container.p-4.mb-3
            label(v-text="$t('dashboard.name')")
            .input-group
                input.form-control(type="text", v-model="name")
        h2 {{ $t('dashboard.Statuses') }}
        div.row(style="display:flex;flex-direction: row;flex-wrap:wrap;")
            entity(v-for="ent in $store.getters['dashboard/entities']", :id="ent.id", :key="ent.id")
        h2 {{ $t('dashboard.Widgets') }}
        div.row(style="display:flex;flex-direction: row;flex-wrap:wrap;")
            widget(v-for="widget in $store.getters['dashboard/widgets']", :id="widget.id", :size="widget.size", :key="widget.id")
            div.col-12.col-md-3.mb-3(v-if="config && $store.getters['dashboard/widgets'].length < config.max_widgets")
                div.diga-container.p-4.clickable.text-center.add-widget(v-on:click="add_widget")
                    table.h-100.w-100.text-center
                        tr
                            td
                                div(style="font-size: 50px;")
                                    i.fa.fa-plus-circle
                                div {{ $t('dashboard.Add_widget') }}
        div.row
            fieldset.form-group
                .col-12
                    button.btn.btn-diga(v-on:click="save_data()") {{ $t('template.Save') }}
</template>

<script>
import entity from './entity.vue'
import widget from './widget.vue'
import dashboardStore from './../../vuex/modules/dashboard.js'

export default {
    props: ['id'],
    data() {
        return {
            name: '',
            loading: false,
            config: null,
        }
    },
    created() {
        this.$store.registerModule('dashboard', dashboardStore);

        this.load_config().then(() => {
            this.$store.dispatch('dashboard/field_types', this.config.entity_field_types);
            let data = {
                'field_types': this.config.entity_field_types,
                'statuses': this.$store.getters.getNotRemovedServiceStates,
                'max_widgets': this.config.max_widgets,
            };
            if (this.id) {
                document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('dashboard.edit_title');
                this.load_dashboard().then(res => {
                    data.dashboard = res;
                    this.name = data.dashboard.name;
                    this.$store.dispatch('dashboard/storedData', data);
                });
            } else {
                document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('dashboard.create_title');
                data.dashboard = {};
                this.name = '';
                this.$store.dispatch('dashboard/initialData', data);
            }
        });
    },
    beforeDestroy(){
        this.$store.unregisterModule('dashboard');
    },
    components: {
        entity, widget,
    },
    watch: {
        name(val) {
            this.$store.dispatch('dashboard/dashboardName', val);
        },
    },
    methods: {
        add_widget(){
            this.$store.dispatch('dashboard/addWidget');
        },
        load_dashboard(){
            this.$root.global_loading = true;
            return new Promise((resolve, reject) => {
                this.$http.get('/api/dashboards/' + this.id).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        reject();
                    } else {
                        resolve(res.data);
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                    reject();
                });
            });
        },
        load_config(){
            this.$root.global_loading = true;
            return new Promise((resolve, reject) => {
                this.$http.get('/api/dashboards/config').then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        reject();
                    } else {
                        this.config = res.data;
                        resolve();
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                    reject();
                });
            });
        },
        save_data() {
            this.$root.global_loading = true;
            var payload = {};
            payload.name = this.name;
            payload.entities = this.$store.getters['dashboard/entities_for_store'];
            payload.widgets = this.$store.getters['dashboard/widgets'];

            if (!this.id) {
                this.$http.post('/api/dashboards', payload).then(res => {
                    if (res.body.OK) {
                        this.$toastr.s(this.$root.$t("dashboard.dashboard_saved"), this.$root.$t("template.Success"));
                        this.$router.push({name: 'dashboards_settings'});
                    }
                    this.$root.global_loading = false;
                }, res => {
                    if (!res.body.OK) {
                        var errors = res.body.errors;
                        for (var key in errors) {
                            var messages = errors[key];
                            for (var i = 0; i < messages.length; i++) {
                                this.$toastr.e(errors[key][i], this.$root.$t("template.Error"));
                            }
                        }
                    }
                    this.$root.global_loading = false;
                });
            } else {
                this.$http.put('/api/dashboards/' + this.id, payload).then(res => {
                    if (res.body.OK) {
                        this.$toastr.s(this.$root.$t("dashboard.dashboard_saved"), this.$root.$t("template.Success"));
                        this.loading = false;
                        this.$router.push({ name: 'dashboards_settings' });
                    }
                    this.$root.global_loading = false;
                }, res => {
                    if (!res.body.OK) {
                        var errors = res.body.errors;
                        for (var key in errors) {
                            var messages = errors[key];
                            for (var i = 0; i < messages.length; i++) {
                                this.$toastr.e(errors[key][i], this.$root.$t("template.Error"));
                            }
                        }
                    }
                    this.$root.global_loading = false;
                });
            }
        },
    },
}

</script>