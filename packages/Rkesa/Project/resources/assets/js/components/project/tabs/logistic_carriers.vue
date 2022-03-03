<style>
    .carriers-list .toggle{
        margin-left: 5px;
        min-width: 100px;
    }
</style>

<template lang="pug">
    div.mb-2
        div.project-section {{ $t('project.Transportation_info') }}
        div.row
            div.col-12.col-lg-8
                div.carriers-list(v-for="carrier in order.carriers")
                    div.form-group.row
                        div.col-6
                            div.row
                                label.col-sm-5.col-form-label {{ $t('project.Carrier_expiditor') }}
                                div.col-sm-7.d-flex
                                    input.form-control.mr-2(v-if="carrier.carrier_id" style="flex:1;" v-model="carrier.carrier.name" disabled)
                                    v-select.w-100.mr-2(v-else
                                        style="flex: 1;",
                                        v-model='carrier.carrier',
                                        label="name",
                                        :debounce='250',
                                        :on-change='(val) => { carrier_select(val, carrier)}',
                                        :on-search='get_carriers_options',
                                        :options='curr_carriers_list',
                                        :disabled="project.finished || !$root.can_with_project('update', 2)",
                                        v-bind:placeholder="$t('estimate.Escolha_a_opcao')")
                                        template(slot="no-options") {{ $t('template.No_matching_options') }}
                        div.col-6
                            div.row
                                label.col-sm-4.col-form-label {{ $t('project.Agreement') }}
                                div.col-sm-8.input-line.d-flex
                                    template(v-if="carrier.from_db")
                                        select.form-control(v-model="carrier.carrier_contract_id", :disabled="project.finished || !$root.can_with_project('update', 2)")
                                            option(v-for="contract in carrier.carrier.contracts", :value="contract.id") {{ contract.name }}
                                    template(v-else)
                                        file-uploader(
                                            :file_url="carrier.contract_file"
                                            :file_name="carrier.contract_file_name"
                                            :editable="!project.finished && $root.can_with_project('update', 2)"
                                            @remove="remove_contract_file(carrier)"
                                            @finished="(arr) => { [carrier.contract_file, carrier.contract_file_name] = arr }")
                                    bootstrap-toggle(v-model="carrier.from_db", :options="{ on: $t('project.From_db'), off: $t('project.File')}", data-width="100", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 2)")
                                    button.btn.btn-danger.ml-2(style="height: 36px;" @click="remove_carrier(carrier)" :disabled="project.finished || !$root.can_with_project('update', 2)")
                                        i.fa.fa-times
                button.btn.btn-diga(v-on:click="add_carrier", :disabled="project.finished || !$root.can_with_project('update', 2)") {{ $t('template.Add') }}
            div.col-12.col-lg-4
                h6 {{ $t('project.Comment') }}
                textarea.form-control(v-model="order.comment_carrier", :disabled="project.finished || !$root.can_with_project('update', 2)")
</template>

<script>
export default {
    props: {
        project: Object,
        order: Object,
    },
    data: function(){
        return {
            curr_carriers_list: [],
        }
    },
    methods: {
        get_carriers_options(search, loading) {
            loading(true);
            this.$http.get('/api/carriers?limit=9999&query=' + search).then(res => {
                this.curr_carriers_list = res.data.rows;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                loading(false);
            })
        },
        carrier_select(val, item){
            if (val !== null) {
                item.carrier_id = val.id;
                item.carrier = val;
            } else {
                item.carrier_id = null;
                item.carrier = null;
            }
        },
        add_carrier(){
            let newCarrier = {
                id: 0,
                carrier_id: null,
                carrier: {
                    name: '',
                },
                contract_number: '',
                contract_file: null,
                contract_file_name: null,
                from_db: false,
            };
            this.order.carriers.push(newCarrier);
        },
        remove_carrier(carrier){
            if (confirm(this.$root.$t("project.Sure_remove_carrier"))){
                if (!('removed_equipment' in this.order)){
                    this.order.removed_carriers = [];
                }
                this.order.removed_carriers.push(carrier.id);
                let index = this.order.carriers.indexOf(carrier);
                this.order.carriers.splice(index, 1);
            }
        },
        remove_contract_file(carrier){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                carrier.contract_file = null;
                carrier.contract_file_name = null;
            }
        },
    },
}
</script>