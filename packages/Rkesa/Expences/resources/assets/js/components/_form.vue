<template lang="pug">
    section.diga-container.p-4
        h2 {{ isCreating ? $t('expences.new_expence') : $t('service.Service_edit') }}
        .row(v-if="currentExpence")
            section.col-12.col-md-4
                fieldset.form-group(:class="{ 'has-error': errors.has('invoice_n') }")
                    label {{ $t('project.Invoice') }} №
                    input.form-control(name="invoice_n", v-validate="'required'", type="text", v-model="currentExpence.invoice_number", v-bind:data-vv-as="$t('project.Invoice').toLowerCase()")
                    h6.help-block(v-show="errors.has('invoice_n')") {{ errors.first('invoice_n') }}

            section.col-12.col-md-4
                fieldset.form-group(:class="{ 'has-error': errors.has('supplier') }")
                    label {{ $t('expences.supplier') }}
                    input.form-control(name="supplier", v-validate="'required'", type="text", v-model="currentExpence.supplier", v-bind:data-vv-as="$t('expences.supplier').toLowerCase()")
                    h6.help-block(v-show="errors.has('supplier')") {{ errors.first('supplier') }}

            section.col-12.col-md-4
                fieldset.form-group
                    label {{ $t('project.Date') }}
                    date-picker(format="YYYY-MM-DD" v-model="currentExpence.date", :lang="$root.locale", :width="'100%'")

            section.col-12.col-md-4
                fieldset.form-group(:class="{ 'has-error': errors.has('total_without_vat') }")
                    label {{ $t('client.base_value') }}
                    input.form-control(v-validate="'required'" name="total_without_vat" type="number", step="0.01", v-model="currentExpence.total_without_vat" v-bind:data-vv-as="$t('client.base_value').toLowerCase()")
                    h6.help-block(v-show="errors.has('total_without_vat')") {{ errors.first('total_without_vat') }}
            section.col-12.col-md-4
                fieldset.form-group(:class="{ 'has-error': errors.has('vat_type') }")
                    label {{ $t('client.type_of_the_vat') }}%
                    input.form-control(v-validate="'required'" name="vat_type" type="number", v-model="currentExpence.vat_type" v-bind:data-vv-as="$t('client.type_of_the_vat').toLowerCase()")
                    h6.help-block(v-show="errors.has('vat_type')") {{ errors.first('vat_type') }}
            section.col-12.col-md-4
                fieldset.form-group(:class="{ 'has-error': errors.has('total') }")
                    label {{ $t('client.total') }}
                    input.form-control(v-validate="'required'" name="total" type="number", v-model="currentExpence.total" v-bind:data-vv-as="$t('client.total').toLowerCase()")
                    h6.help-block(v-show="errors.has('total')") {{ errors.first('total') }}

            section.col-12.col-md-4
                fieldset.form-group
                    label {{ $t('project.Invoice') }}
                    br
                    file-uploader(
                        :file_url="currentExpence.invoice_file"
                        :file_name="currentExpence.invoice_file_name"
                        :editable="true"
                        @remove="remove_invoice_file(currentExpence)"
                        @finished="(arr) => { [currentExpence.invoice_file, currentExpence.invoice_file_name] = arr }")                       
               
            section.col-12.col-md-4
                fieldset.form-group
                    label {{ $t('client.Checking_account') }}
                    v-select(style="width: 100%;",
                        :debounce='250',
                        :on-search='get_base_options',
                        :on-change='base_select',
                        :options='bases',
                        :placeholder="$t('estimate.Choose_estimate')")
            //- section.col-12.col-md-4
            //-     fieldset.form-group
            //-         label {{ $t('client.Correspondent_account') }}
            //-         input.form-control(type="text", v-model="currentExpence.correspondent_account")
        button.btn.btn-diga(style="margin: 10px 0 0 0" v-on:click="store_expence()") {{ $t("project.Save") }}
</template>

<script>
import moment from 'moment';

export default {
    props: ['id'],
    data(){
        return {
            currentExpence: null,
            isCreating: true,
            bases: [],
        }
    },
    created(){
        if (this.id) {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('service.Service_edit');
            this.load_expence();
        } else {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('expences.new_expence');
            let newExpence = {
                invoice_number: '',
                supplier: '',
                date: moment().startOf('day'),
                total_without_vat: 0.0,
                vat_type: 0,
                total: 0,
                invoice_file: null,
                invoice_file_name: null,
                estimate_id: null
            };
            this.currentExpence = Object.assign({}, newExpence);
        }
    },
    methods: {
        load_expence(){
            this.$root.global_loading = true;
            this.$http.get('/api/expences/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.currentExpence = res.data;
                    this.isCreating = false;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        store_expence(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.$root.global_loading = true;
                if (this.isCreating) {
                    this.$http.post('/api/expences', this.currentExpence).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Request_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'expences_show', params: {id: res.data.id}});
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                } else {
                    this.$http.patch('/api/expences/' + this.id, this.currentExpence).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Request_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'expences_show', params: {id: this.id}});
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                }
            });
        },
        remove_invoice_file(expence){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                expence.invoice_file = null;
                expence.invoice_file_name = null;
            }
        },
        get_base_options(search, loading) {
            loading(true);
            this.$http.get('/api/user_plannings?search=' + search).then(res => {
                var processedData = [];
                let $this = this;
                res.data.forEach(function(i){
                    processedData.push({'label': $this.$root.estimate_number(i), 'value': i.id});
                });
                this.bases = processedData;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            })
        },
        base_select(res){
            if(res === null){
                this.currentExpence.estimate_id = null;
            }
            if (typeof res === 'object' && res !== null) {
                this.currentExpence.estimate_id = res.value;
            }
        },
    },
    watch: {
        currentExpence: {
            deep: true,
            handler(newE){
                this.currentExpence.total = parseFloat(newE.total_without_vat) + parseFloat(newE.total_without_vat * newE.vat_type / 100);
            }            
        }
    }
}
</script>