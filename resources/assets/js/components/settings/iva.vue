<style>
</style>

<template lang="pug">
    div
        div.row
            div.col-12.col-md-6.mb-3(v-if="vat_types")
                div.diga-container.p-4
                    h2 {{ $t('client.vat') }}
                    table.referrers-table
                        tr
                            th #
                            th {{ $t('template.code') }}
                            th {{ $t('calendar.Name') }}
                            th {{ $t('estimate.Percent') }}
                        tr
                            td  0.
                            td
                                input.form-control(value="NO" disabled="true")
                            td  
                                input.form-control(:value="$t('template.regime_of_exemption_vat')" disabled="true")
                            td  
                                input.form-control(value="0" disabled="true")

                        tr(v-for="(vat_type,i) in vat_types.filter(vt =>  vt.code !== 'NO')" style="margin-bottom: 5px;")
                            td
                                | {{ i+1 }}.
                            td
                                input.form-control(v-model="vat_type.code")
                            td
                                input.form-control(v-model="vat_type.name")
                            td
                                input.form-control(v-model="vat_type.percent" type="number" step="0.1")
                            td
                                button.btn.red(v-on:click="remove_vat_type(vat_type)") {{ $t('template.Remove') }}
                    button.btn.btn-diga.mt-2(v-on:click="add_vat_type()") {{ $t('template.Add') }}        
        div.row
            div.col-12
                button.btn.btn-diga(v-on:click="save_settings()" style="margin-right: 20px;") {{ $t('template.Save') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    data() {
        return {
            vat_types: [],
            removed_vat_types: [],
        }
    },
    created(){
        this.get_vat_types();
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('client.vat');
    },
    computed: {
    },
    methods: {
        get_vat_types(){
            this.$root.global_loading = true;
            this.$http.get('/api/vat_types').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.vat_types = res.data.rows;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });            
        },
        save_settings(){
            this.$root.global_loading = true;
            let payload = {
                vat_types: this.vat_types,
                removed_vat_types: this.removed_vat_types,
            };

            this.$http.post('/api/vat_type_settings', payload).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.$toastr.s(this.$root.$t("template.Settings_saved"), this.$root.$t("template.Success"));
                    this.removed_vat_types = [];
                    this.get_vat_types();
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });            
        },
        remove_vat_type(vat_type){
            if (confirm(this.$root.$t("calendar.AreYouSure"))){
                this.removed_vat_types.push(vat_type.id);
                let index = this.vat_types.indexOf(vat_type);
                this.vat_types.splice(index, 1);
            }
        },
        add_vat_type(){
            let vat_type = {
                id: 0,
                name: '',
                days: 0
            };
            this.vat_types.push(vat_type);
        },
    },
}
</script>