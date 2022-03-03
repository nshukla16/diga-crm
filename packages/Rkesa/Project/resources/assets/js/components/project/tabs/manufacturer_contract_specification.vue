<template lang="pug">
    div.mb-2
        div.project-section {{ $t('project.Contract_or_specification') }}
        div.row
            div.col-12
                div.row
                    div.col-6
                        div.form-group.row
                            label.col-4.input-line {{ $t('project.Contract_number') }}
                            div.col-8.d-flex
                                //template(v-if="!manufacturer.manufacturer_contract_id")
                                    input.form-control.mr-2(style="flex: 1;" type="text", v-model="manufacturer.contract_number", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                    div.input-line.mr-2(style="flex: 1;")
                                        file-uploader(
                                            :file_url="manufacturer.contract_file"
                                            :file_name="manufacturer.contract_file_name"
                                            :editable="!project.finished && $root.can_with_project('update', 1)"
                                            @remove="remove_contract_file"
                                            @finished="(arr) => { [manufacturer.contract_file, manufacturer.contract_file_name] = arr }")
                                //template(v-else)
                                select.form-control.mr-2(style="flex: 1;" v-model="manufacturer.manufacturer_contract_id", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                    option(v-for="contract in manufacturer.manufacturer.contracts", :value="contract.id") {{ contract.name }}
                                div.mr-2(v-if="manufacturer.manufacturer_contract_id" style="flex:1;")
                                    a.input-line.short-link(:href="current_contract.file" target="_blank", :title="current_contract.file_name") {{ current_contract.file_name }}
                                //div(style="width:100px;" @click="from_bd_toggle()")
                                    bootstrap-toggle(v-model="from_bd", :options="{ on: $t('project.From_db'), off: $t('project.File')}", data-width="100", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 1)")
                    div.col-6
                        div.form-group.row
                            label.col-6.input-line {{ $t('project.Contract_signed_date') }}
                            div.col-6
                                date-picker.mx-2(:lang="$root.locale" v-model="manufacturer.contract_signed_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140", :disabled="project.finished || !$root.can_with_project('update', 1)")
                div.form-group
                    div(v-for="spec in manufacturer.specifications")
                        div.row
                            div.col-6
                                div.form-group.row
                                    label.col-4.input-line {{ $t('project.Specification_number') }}
                                    div.col-8.d-flex
                                        select.form-control.mr-2(style="flex:1;", v-model="spec.manufacturer_contract_id", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                            option(v-for="contract in manufacturer.manufacturer.contracts", :value="contract.id") {{ contract.name }}
                                        div.mr-2(v-if="spec.manufacturer_contract_id" style="flex:1;")
                                            a.input-line.short-link(:href="current_addit_contract(spec).file" target="_blank", :title="current_addit_contract(spec).file_name") {{ current_addit_contract(spec).file_name }}
                                        //template(v-if="spec.from_db")
                                        //template(v-else)
                                            input.form-control.mr-2(style="flex: 1;" type="text", v-model="spec.number", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                            div.input-line.mr-2(style="flex: 1;")
                                                file-uploader(
                                                    :file_url="spec.file"
                                                    :file_name="spec.file_name"
                                                    :editable="!project.finished && $root.can_with_project('update', 1)"
                                                    @remove="remove_specification_file(spec)"
                                                    @finished="(arr) => { [spec.file, spec.file_name] = arr }")
                                        //div(style="width:100px;" @click="select_first_contract(spec)")
                                            bootstrap-toggle(v-model="spec.from_db", :options="{ on: $t('project.From_db'), off: $t('project.File')}", data-width="100", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            div.col-6
                                div.form-group.row
                                    label.col-6.input-line {{ $t('project.Specification_signed_date') }}
                                    div.col-6
                                        date-picker.mx-2(:lang="$root.locale" v-model="spec.signed_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                        button.btn.btn-danger(v-on:click="remove_spec(spec)", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                            i.fa.fa-times
                    button.btn.btn-diga(v-on:click="add_spec", :disabled="project.finished || !$root.can_with_project('update', 1)") {{ $t('project.Add_specification') }}
                div.row
                    div.col-6
                        div.form-group.row
                            label.col-6.input-line {{ $t("project.Conditions_of_delivery_for_contract") }}
                            div.col-6
                                select.form-control(v-model="manufacturer.conditions_of_delivery", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                    option(:value="i" v-for="(v,i) in conditions") {{ v }}
            div.col-12.col-lg-6
                div.row.mb-2(v-for="contract in manufacturer.additional_contracts")
                    label.col-4.input-line {{ $t('project.Additional_contract_number') }}
                    div.col-8.d-flex.input-line
                        template(v-if="manufacturer.manufacturer.contracts.length > 0")
                            select.form-control.mr-2(style="flex:1;", v-model="contract.manufacturer_contract_id", :disabled="project.finished || !$root.can_with_project('update', 1)")
                                option(v-for="contract in manufacturer.manufacturer.contracts", :value="contract.id") {{ contract.name }}
                            div.mr-2(v-if="contract.manufacturer_contract_id" style="flex:1;")
                                a.input-line.short-link(:href="current_addit_contract(contract).file" target="_blank", :title="current_addit_contract(contract).file_name") {{ current_addit_contract(contract).file_name }}
                        div(v-else style="margin-left: auto;") {{ $t('project.No_contracts') }}
                        //template(v-if="contract.from_db")
                        //template(v-else)
                            input.form-control.mr-2(style="flex: 1;" type="text", v-model="contract.contract_number", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            div.mr-2(style="flex: 1;")
                                file-uploader(
                                    :file_url="contract.contract_file"
                                    :file_name="contract.contract_file_name"
                                    :editable="!project.finished && $root.can_with_project('update', 1)"
                                    @remove="remove_addit_contract_file(contract)"
                                    @finished="(arr) => { [contract.contract_file, contract.contract_file_name] = arr }")
                        //div(style="width:100px;" @click="select_first_contract(contract)")
                            bootstrap-toggle(v-model="contract.from_db", :options="{ on: $t('project.From_db'), off: $t('project.File')}", data-width="100", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 1)")
                        button.btn.btn-danger.ml-2(@click="remove_addit_contract(contract)", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            i.fa.fa-times
                button.btn.btn-diga(@click="add_addit_contract", :disabled="project.finished || !$root.can_with_project('update', 1)") {{ $t('project.Add_additional_contract') }}
</template>

<script>
import {manufacturer_conditions_of_delivery} from '../../../helper';

export default {
    props: {
        project: Object,
        manufacturer: Object,
    },
    data: function() {
        return {
            from_bd: this.manufacturer.manufacturer_contract_id != null,
        }
    },
    computed: {
        current_contract(){
            return this.manufacturer.manufacturer.contracts.find(c => c.id == this.manufacturer.manufacturer_contract_id);
        },
        conditions(){
            return manufacturer_conditions_of_delivery;
        },
    },
    methods: {
        select_first_contract(contract){
            if (!contract.from_db && contract.manufacturer_contract_id == null){
                contract.manufacturer_contract_id = this.manufacturer.manufacturer.contracts[0].id;
            }
        },
        current_addit_contract(contract){
            return this.manufacturer.manufacturer.contracts.find(c => c.id == contract.manufacturer_contract_id);
        },
        add_addit_contract(){
            let newAddit = {
                id: 0,
                contract_number: '',
                contract_file: null,
                contract_file_name: null,
                from_db: false,
                manufacturer_contract_id: null,
            };
            this.manufacturer.additional_contracts.push(newAddit);
        },
        remove_addit_contract(contract){
            if (confirm(this.$root.$t("project.Sure_remove_addit_contract"))){
                if (!('removed_addit_contracts' in this.manufacturer)){
                    this.manufacturer.removed_addit_contracts = [];
                }
                this.manufacturer.removed_addit_contracts.push(contract.id);
                let index = this.manufacturer.additional_contracts.indexOf(contract);
                this.manufacturer.additional_contracts.splice(index, 1);
            }
        },
        remove_addit_contract_file(contract){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                contract.contract_file = null;
                contract.contract_file_name = null;
            }
        },
        from_bd_toggle(){
            if (this.from_bd){
                this.manufacturer.manufacturer_contract_id = null;
            } else {
                this.manufacturer.manufacturer_contract_id = this.manufacturer.manufacturer.contracts[0].id;
            }
        },
        remove_contract_file(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.manufacturer.contract_file = null;
                this.manufacturer.contract_file_name = null;
            }
        },
        remove_specification_file(spec){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                spec.file = null;
                spec.file_name = null;
            }
        },
        add_spec(){
            let newSpec = {
                id: 0,
                number: '',
                file: null,
                file_name: null,
                signed_date: '',
            };
            this.manufacturer.specifications.push(newSpec);
        },
        remove_spec(spec){
            if (confirm(this.$root.$t("project.Sure_remove_specification"))){
                if (!('removed_specifications' in this.manufacturer)){
                    this.manufacturer.removed_specifications = [];
                }
                this.manufacturer.removed_specifications.push(spec.id);
                let index = this.manufacturer.specifications.indexOf(spec);
                this.manufacturer.specifications.splice(index, 1);
            }
        },
        uploading_error(e){
            this.$toastr.e(e, this.$root.$t("template.Error"));
        },
    },
}
</script>