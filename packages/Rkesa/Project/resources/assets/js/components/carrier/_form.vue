<template lang="pug">
    section.diga-container.p-4
        h2 {{ isCreating ? $t('project.Carrier_new') : $t('project.Carrier_edit') }}
        .row(v-if="currentCarrier")
            section.col-12.col-md-4
                fieldset.form-group(:class="{ 'has-error': errors.has('name') }")
                    label {{ $t('project.Name') }}
                    input.form-control(name="name", v-validate="'required'", type="text", v-model="currentCarrier.name", v-bind:data-vv-as="$t('project.Name').toLowerCase()")
                    h6.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
        button.btn.btn-diga(style="margin: 10px 0 0 0" v-on:click="store_carrier()") {{ $t("project.Save") }}
</template>

<script>

export default {
    props: ['id'],
    data(){
        return {
            currentCarrier: null,
            isCreating: true,
        }
    },
    created(){
        if (this.id) {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Carrier_edit');
            this.load_carrier();
        } else {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Carrier_new');
            let newCarrier = {
                name: '',
            };
            this.currentCarrier = Object.assign({}, newCarrier);
        }
    },
    methods: {
        load_carrier(){
            this.$root.global_loading = true;
            this.$http.get('/api/carriers/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.currentCarrier = res.data;
                    this.isCreating = false;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        store_carrier(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.$root.global_loading = true;
                if (this.isCreating) {
                    this.$http.post('/api/carriers', this.currentCarrier).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Carrier_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'carrier_show', params: {id: res.data.id}});
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                } else {
                    this.$http.patch('/api/carriers/' + this.id, this.currentCarrier).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Carrier_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'carrier_show', params: {id: this.currentCarrier.id}});
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