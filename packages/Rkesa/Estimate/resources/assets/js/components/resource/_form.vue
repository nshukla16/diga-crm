<style>

</style>

<template lang="pug">
    .portlet.light(v-if="currentResource")
        .portlet-body
            .row
                section.col-12.col-md-6.mb-3
                    div.diga-container.p-4
                        h2 {{ $t('estimate.Main_information') }}
                        fieldset.form-group(:class="{ 'has-error': errors.has('name') }")
                            label.control-label {{ $t('estimate.Name') }}
                            input.form-control(name="name", v-validate="'required'", type="text", v-model="currentResource.name" v-bind:data-vv-as="$t('estimate.Name').toLowerCase()")
                            span.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
                        fieldset.form-group
                            label.control-label {{ $t('estimate.Resource_type') }}
                            select(class="form-control", v-model="currentResource.resource_type")
                                option(:value="0") {{ $t('estimate.Mao_de_obra') }}
                                option(:value="1") {{ $t('estimate.Materiais') }}
                                option(:value="2") {{ $t('estimate.Equipamentos') }}
                                option(:value="3") {{ $t('estimate.Subempreitadas') }}
                        div.row
                            div.col-12.col-md-6
                                fieldset.form-group(:class="{ 'has-error': errors.has('price') }")
                                    label.control-label {{ $t('estimate.Price_for_1_unit')+' '+units_by_id[currentResource.estimate_unit_id].measure }}
                                    input.form-control(name="price", v-validate="'min_value:0'", type="number", v-model="currentResource.price" min="0")
                                    span.help-block(v-show="errors.has('price')") {{ errors.first('price') }}
                            div.col-12.col-md-6
                                fieldset.form-group
                                    label.control-label {{ $t('estimate.Unit') }}
                                    select(class="form-control", v-model="currentResource.estimate_unit_id")
                                        option(v-for="unit in units", :value="unit.id") {{ unit.measure }}
                        div.row
                            div.col-12.col-md-6
                                fieldset.form-group(:class="{ 'has-error': errors.has('quantity') }")
                                    label.control-label {{ get_caption_of_efficiency }} ( {{ units_by_id[currentResource.estimate_unit_id].measure }}/{{ units_by_id[currentResource.efficiency_estimate_unit_id].measure }} )
                                    input.form-control(name="quantity", v-validate="'min_value:0'", type="number", v-model="currentResource.quantity" min="0")
                                    span.help-block(v-show="errors.has('quantity')") {{ errors.first('quantity') }}
                            div.col-12.col-md-6
                                fieldset.form-group
                                    label.control-label {{ $t('estimate.Expence_unit') }}
                                    select(class="form-control", v-model="currentResource.efficiency_estimate_unit_id")
                                        option(v-for="unit in units", :value="unit.id") {{ unit.measure }}
                        fieldset.form-group
                            label.control-label {{ $t('estimate.Update_fichas') }}
                            input.form-control(type="checkbox" v-model="currentResource.update_fichas" style="width: auto;")
                section.col-12.col-md-6.mb-3
                    div.diga-container.p-4
                        h2 {{ $t('estimate.Documents') }}
                        div.table-responsive
                            table.table.table-hover
                                thead
                                    tr
                                        th(style="width: 32px;") №
                                        th {{ $t('estimate.Preview') }}
                                        th {{ $t('estimate.Name') }}
                                        th {{ $t('estimate.Actions') }}
                                tbody
                                    tr(v-for="(attachment,index) in currentResource.resource_attachments")
                                        td {{ index+1 }}
                                        td
                                            template(v-if="extension(attachment.url) == '.pdf'")
                                                pdf(v-bind:src="attachment.url" style="width:100px")
                                            template(v-else)
                                                img(v-bind:src="attachment.url" style="width:100px")
                                        td
                                            a(v-bind:href="attachment.url") {{ attachment.name+extension(attachment.url) }}
                                        td
                                            a.btn.red.btn-sm.uppercase(v-on:click="delete_attachment(attachment)")
                                                i.fa.fa-trash-o  {{ $t('estimate.Delete') }}
                        vue-core-image-upload(
                            v-show="!loading",
                            :class="['btn', 'btn-diga']",
                            @imageuploading="imageuploading",
                            @imageuploaded="imageuploaded",
                            @errorhandle="imageerror",
                            :headers="{Authorization: $root.access_token}",
                            :extensions="'.pdf'",
                            :inputAccept="'.pdf'",
                            :max-file-size="$root.max_file_size",
                            :text="$t('estimate.Upload_document')"
                            url="/api/file_upload")
                        div(v-show="loading")
                            div.loader.sm-loader
            .row
                .col-12
                    button.btn.btn-diga(v-on:click="save") {{ $t('template.Save') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    data: function() {
        return {
            currentResource: null,
            loading: false,
            isCreating: true,
        }
    },
    props: ['id'],
    computed: {
        ...mapGetters({
            units: 'getEstimateUnits',
            units_by_id: 'getEstimateUnitsById',
        }),
        get_caption_of_efficiency(){
            switch (this.currentResource.resource_type){
            case 0:
                return this.$root.$t('estimate.Performance');
            case 1:
                return this.$root.$t('estimate.Rendimento');
            case 2:
                return this.$root.$t('estimate.Efficiency');
            case 3:
                return this.$root.$t('estimate.Efficiency');
            }
        },
    },
    methods: {
        load_resource(){
            this.$root.global_loading = true;
            this.$http.get('/api/resources/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.currentResource = res.data;
                    this.isCreating = false;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        save: function(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                let payload = Object.assign({}, this.currentResource);
                if (this.isCreating) {
                    this.$http.post('/api/resources', payload).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("estimate.Resource_saved"), this.$root.$t("template.Success"));
                            this.$router.push({ name: 'resources_index' });
                        }
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    });
                } else if ((this.currentResource.update_fichas && confirm(this.$root.$t("estimate.Are_you_sure_want_to_update_resource"))) || !this.currentResource.update_fichas) {
                    this.$http.patch('/api/resources/' + this.currentResource.id, payload).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("estimate.Resource_saved"), this.$root.$t("template.Success"));
                            this.$router.push({ name: 'resources_index' });
                        }
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    });
                }
            });
        },
        imageuploading: function(){
            this.loading = true;
        },
        imageuploaded: function(res){
            this.loading = false;
            if (res.errcode == 0) {
                let attachment = {
                    name: res.name,
                    url: res.url,
                }
                this.currentResource.resource_attachments.push(attachment);
            } else {
                this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
            }
        },
        imageerror(e){
            this.$toastr.e(e, this.$root.$t("template.Error"));
        },
        delete_attachment: function(at){
            if (confirm(this.$root.$t("estimate.Are_you_sure_want_to_delete_this"))) {
                let i = this.currentResource.resource_attachments.indexOf(at);
                this.currentResource.resource_attachments.splice(i, 1);
            }
        },
        extension: function(str){
            return str.substring(str.lastIndexOf('.'));
        },
    },
    mounted(){
        if (this.id) {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('estimate.Edit_resource');
            this.load_resource();
        } else {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('estimate.New_resource');
            let newResource = {
                name: '',
                quantity: 1,
                estimate_unit_id: this.units[0].id,
                efficiency_estimate_unit_id: this.units[0].id,
                price: 0,
                resource_type: 0,
                resource_attachments: [],
            };
            this.isCreating = true;
            this.currentResource = Object.assign({}, newResource);
        }
    },
}
</script>