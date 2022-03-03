<style>
    .without-margin-bottom{
        margin-bottom: 0;
    }
    .pointer{
        cursor: pointer;
    }
</style>

<template lang="pug">
    section.diga-container.p-4(v-if="currentEstimateFork")
        label {{ $t('estimate_fork.Name') }}
        input.form-control(v-model="currentEstimateFork.name")
        br
        table.table.table-bordered.table-striped
            thead
                tr
                    th(style="width: 20px") №
                    th(style="width: 150px") {{ $t('estimate_fork.Object') }}
                    th {{ $t('estimate_fork.Rules') }}
            tbody
                template(v-for="(fork_entity,i) in currentEstimateFork.fork_entities")
                    tr
                        td
                            | {{ fork_entity.order }}
                            i.fa.fa-chevron-up.pointer(v-on:click="to_up(fork_entity)")
                            i.fa.fa-chevron-down.pointer(v-on:click="to_down(fork_entity)")
                            i.fa.fa-times.pointer(v-on:click="remove_entity(fork_entity)")
                        td
                            select.form-control(v-model="fork_entity.object")
                                option(value="1") {{ $t('estimate_fork.Artigo') }}
                                option(value="2") {{ $t('estimate_fork.Ficha') }}
                                option(value="4") {{ $t('estimate_fork.Category') }}
                                option(value="3") {{ $t('estimate_fork.Subcategory') }}
                        td
                            table.table.table-bordered.table-striped.without-margin-bottom(v-if="fork_entity.object == 1 || fork_entity.object == 2")
                                thead
                                    tr
                                        th(style="width: 150px") {{ $t('estimate_fork.Field') }}
                                        th(style="width: 150px") {{ $t('estimate_fork.Rule') }}
                                        th(style="width: 150px") {{ $t('estimate_fork.Subject') }}
                                        th(style="width: 1%;") {{ $t('template.Remove') }}
                                tbody
                                    tr(v-for="entity_rule in fork_entity.entity_rules")
                                        td
                                            select.form-control(v-model="entity_rule.field")
                                                option(value="1") {{ $t('estimate_fork.Description') }}
                                                option(value="2") {{ $t('estimate_fork.Price') }}
                                                option(v-if="fork_entity.object == 2" value="3") {{ $t('estimate_fork.Resource') }}
                                        template(v-if="entity_rule.field == 3")
                                            td(colspan="2")
                                                table.table.table-bordered.table-striped.without-margin-bottom
                                                    thead
                                                        tr
                                                            th {{ $t('estimate_fork.Field') }}
                                                            th {{ $t('estimate_fork.Rule') }}
                                                            th {{ $t('estimate_fork.Subject') }}
                                                            th {{ $t('template.Remove') }}
                                                    tbody
                                                        tr(v-for="condition in entity_rule.resources")
                                                            td
                                                                select.form-control(v-model="condition.field")
                                                                    option(value="1") {{ $t('estimate_fork.Name') }}
                                                                    option(value="2") {{ $t('estimate_fork.Price') }}
                                                            td
                                                                select.form-control(v-model="condition.rule_type")
                                                                    option(value="1") {{ $t('estimate_fork.Equals') }}
                                                                    option(v-if="condition.field != 2" value="2") {{ $t('estimate_fork.Include') }}
                                                            td
                                                                select.form-control(v-if="condition.field == 1 && condition.rule_type == 1" v-model="condition.resource_id")
                                                                    option(v-for="resource in resources" v-bind:value="resource.id") {{ resource.name }}
                                                                input.form-control(v-else v-model="condition.subject")
                                                            td
                                                                button.btn.red(v-on:click="remove_entity_rule_resource(condition, entity_rule)")
                                                                    i.fa.fa-times
                                                    tfoot
                                                        tr
                                                            td(colspan="4")
                                                                button.btn.blue(v-on:click="add_rule_resource(entity_rule)") {{ $t('estimate_fork.Add_condition') }}
                                        template(v-else)
                                            td
                                                select.form-control(v-model="entity_rule.rule_type")
                                                    option(v-if="entity_rule.field != 2" value="1") {{ $t('estimate_fork.Include') }}
                                                    option(value="2") {{ $t('estimate_fork.Equals') }}
                                            td
                                                input.form-control(v-model="entity_rule.subject")
                                        td
                                            button.btn.red(v-on:click="remove_entity_rule(entity_rule, fork_entity)") {{ $t('estimate_fork.Remove_rule') }}
                                tfoot
                                    tr
                                        td(colspan="4")
                                            button.btn.green(v-on:click="add_rule(fork_entity)") {{ $t('estimate_fork.Add_rule') }}
                            table.table.table-bordered.table-striped.without-margin-bottom(v-if="fork_entity.object == 3")
                                thead
                                    tr
                                        th(style="width: 150px") {{ $t('estimate_fork.Category') }}
                                        th(style="width: 150px") {{ $t('estimate_fork.Subcategory') }}
                                tbody
                                    tr
                                        td
                                            input.form-control(v-model="fork_entity.category")
                                        td
                                            input.form-control(v-model="fork_entity.subcategory")
                            template(v-if="fork_entity.object == 4")
                                input.form-control(v-model="fork_entity.category")
                    tr
                        td(colspan="3")
                            table.table.table-bordered.table-striped.without-margin-bottom
                                thead
                                    tr
                                        th {{ $t('estimate_fork.Type') }}
                                        th {{ $t('estimate_fork.Value') }}
                                        th(style="width: 1px;") {{ $t('template.Remove') }}
                                tbody
                                    tr(v-for="entity_change in fork_entity.entity_changes")
                                        td
                                            select.form-control(v-model="entity_change.change_type")
                                                option(value="0") {{ $t('estimate_fork.Select') }}
                                                template(v-if="fork_entity.object == 1 || fork_entity.object == 2")
                                                    option(value="1") {{ $t('estimate_fork.Change_description') }}
                                                    option(value="2") {{ $t('estimate_fork.Change_quantity') }}
                                                    option(value="9") {{ $t('estimate_fork.Change_note') }}
                                                    option(value="11") {{ $t('estimate_fork.Change_unit') }}
                                                template(v-if="fork_entity.object == 2")
                                                    option(v-if="fork_entity.object == 2" value="3") {{ $t('estimate_fork.Add_resource') }}
                                                    option(v-if="fork_entity.object == 2" value="4") {{ $t('estimate_fork.Change_resource') }}
                                                    option(v-if="fork_entity.object == 2" value="5") {{ $t('estimate_fork.Remove_resource') }}
                                                template(v-if="fork_entity.object == 1 || fork_entity.object == 2 || fork_entity.object == 3")
                                                    option(value="6") {{ $t('estimate_fork.Add_ficha') }}
                                                    option(value="7") {{ $t('estimate_fork.Add_artigo') }}
                                                    option(value="8") {{ $t('estimate_fork.Remove_object') }}
                                                template(v-if="fork_entity.object == 4")
                                                    option(value="10") {{ $t('estimate_fork.Add_subcategory') }}
                                        template(v-if="entity_change.change_type == 3")
                                            td
                                                table.table.table-bordered.table-striped.without-margin-bottom
                                                    thead
                                                        tr
                                                            th {{ $t('estimate_fork.Resource') }}
                                                            th {{ $t('estimate.Price') }}
                                                            th {{ $t('estimate.Quantity') }}
                                                            th {{ $t('estimate.Correccao') }}
                                                    tbody
                                                        tr
                                                            td
                                                                select.form-control(v-model="entity_change.resource_id")
                                                                    option(v-for="resource in resources" v-bind:value="resource.id") {{ resource.name }}
                                                            td
                                                                input.form-control(v-model="entity_change.price" type="number")
                                                            td
                                                                input.form-control(v-model="entity_change.quantity" type="number")
                                                            td
                                                                input.form-control(v-model="entity_change.correction" type="number")


                                        template(v-if="entity_change.change_type == 4")
                                            td
                                                table.table.table-bordered.table-striped.without-margin-bottom
                                                    thead
                                                        tr
                                                            th {{ $t('estimate_fork.Resource') }}
                                                            th {{ $t('estimate_fork.Changes') }}
                                                    tbody
                                                        tr
                                                            td
                                                                select.form-control(v-model="entity_change.resource_id")
                                                                    option(v-for="resource in resources" v-bind:value="resource.id") {{ resource.name }}
                                                            td
                                                                table.table.table-bordered.table-striped.without-margin-bottom
                                                                    thead
                                                                        tr
                                                                            th {{ $t('estimate_fork.Field') }}
                                                                            th {{ $t('estimate_fork.Subject') }}
                                                                            th(style="width: 1px;") {{ $t('template.Remove') }}
                                                                    tbody
                                                                        tr(v-for="resource in entity_change.resources")
                                                                            td
                                                                                select.form-control(v-model="resource.field")
                                                                                    option(value="1") {{ $t('estimate.Resource_type') }}
                                                                                    option(value="2") {{ $t('estimate.Price') }}
                                                                                    option(value="3") {{ $t('estimate.Quantity') }}
                                                                                    option(value="4") {{ $t('estimate.Correccao') }}
                                                                            template(v-if="resource.field == 1")
                                                                                td
                                                                                    select.form-control(v-model="resource.resource_id")
                                                                                        option(v-for="resource in resources" v-bind:value="resource.id") {{ resource.name }}
                                                                            template(v-else)
                                                                                td
                                                                                    input.form-control(v-model="resource.subject")
                                                                            td
                                                                                button.btn.red(v-on:click="remove_entity_change_resource(resource, entity_change)")
                                                                                    i.fa.fa-times
                                                                    tfoot
                                                                        tr
                                                                            td(colspan="2")
                                                                                button.btn.blue(v-on:click="add_change_resource(entity_change)") {{ $t('estimate_fork.Add_change_resource') }}
                                        template(v-if="entity_change.change_type == 5")
                                            td
                                                select.form-control(v-model="entity_change.resource_id")
                                                    option(v-for="resource in resources" v-bind:value="resource.id") {{ resource.name }}
                                        template(v-if="entity_change.change_type == 1")
                                            td
                                                textarea.form-control(v-model="entity_change.description")
                                        template(v-if="entity_change.change_type == 2")
                                            td
                                                div.d-flex
                                                    select.form-control(v-model="entity_change.quantity_type")
                                                        option(value="1") {{ $t('estimate_fork.Fixed_value') }}
                                                        option(value="2") {{ $t('estimate_fork.Take_from_another') }}
                                                    input.ml-3.form-control(v-if="entity_change.quantity_type == 2" v-model="entity_change.description")
                                                    input.ml-3.form-control(v-if="entity_change.quantity_type == 1" v-model="entity_change.quantity" type="number")
                                        template(v-if="entity_change.change_type == 6")
                                            td
                                                table.table.table-bordered.table-striped.without-margin-bottom
                                                    thead
                                                        tr
                                                            th {{ $t('estimate.Ficha') }}
                                                            th(v-if="fork_entity.object == 3 || fork_entity.object == 4") {{ $t('estimate_fork.Position') }}
                                                    tbody
                                                        tr
                                                            td
                                                                select.form-control(v-model="entity_change.ficha_id")
                                                                    option(v-for="ficha in fichas" v-bind:value="ficha.id") {{ ficha.name }}
                                                            td(v-if="fork_entity.object == 3 || fork_entity.object == 4")
                                                                select.form-control(v-model="entity_change.position")
                                                                    option(value="1") {{ $t('estimate_fork.Top') }}
                                                                    option(value="2") {{ $t('estimate_fork.Bottom') }}
                                        template(v-if="entity_change.change_type == 7")
                                            td
                                                table.table.table-bordered.table-striped.without-margin-bottom
                                                    thead
                                                        tr
                                                            th {{ $t('estimate.Description') }}
                                                            th {{ $t('estimate.Unit') }}
                                                            th {{ $t('estimate_fork.Price') }}
                                                            th {{ $t('estimate.Quantity') }}
                                                            th {{ $t('estimate.Notes') }}
                                                    tbody
                                                        tr
                                                            td
                                                                textarea.form-control(v-model="entity_change.description")
                                                            td
                                                                select.form-control(v-model="entity_change.estimate_unit_id")
                                                                    option(v-for="unit in units" v-bind:value="unit.id") {{ unit.measure }}
                                                            td
                                                                input.form-control(v-model="entity_change.price")
                                                            td
                                                                input.form-control(v-model="entity_change.quantity")
                                                            td
                                                                textarea.form-control(v-model="entity_change.note")
                                        template(v-if="entity_change.change_type == 0 || entity_change.change_type == 8")
                                            td
                                        template(v-if="entity_change.change_type == 9")
                                            td
                                                textarea.form-control(v-model="entity_change.note")
                                        template(v-if="entity_change.change_type == 10")
                                            td
                                                input.form-control(v-model="entity_change.subcategory")
                                        template(v-if="entity_change.change_type == 11")
                                            td
                                                select.form-control(v-model="entity_change.estimate_unit_id")
                                                    option(v-for="unit in units" v-bind:value="unit.id") {{ unit.measure }}
                                        td
                                            button.btn.red(v-on:click="remove_entity_change(entity_change, fork_entity)") {{ $t('estimate_fork.Remove_change') }}
                                tfoot
                                    tr
                                        td(colspan="3")
                                            button.btn.blue(v-on:click="add_change(fork_entity)") {{ $t('estimate_fork.Add_change') }}
                    tr
                        td(colspan="3" style="height: 30px;background-color: #a7a7a7;")
            tfoot
                tr
                    td(colspan="4")
                        button.btn.green(v-on:click="add_entity()") {{ $t('estimate_fork.Add_entity') }}
        button.btn.btn-diga(v-on:click="save") {{ $t('template.Save') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    data: function() {
        return {
            isCreating: true,
            currentEstimateFork: null,
            resources: [],
            fichas: [],
        }
    },
    props: ['id'],
    created(){
        this.load_resources();
        this.load_fichas();
    },
    mounted(){
        if (this.id) {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('estimate_fork.Edit_estimate_fork');
            this.load_estimate_fork();
        } else {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('estimate_fork.New_estimate_fork');
            let newEstimateFork = {
                name: 'New fork',
                fork_entities: [],
            };
            this.currentEstimateFork = Object.assign({}, newEstimateFork);
        }
    },
    computed: {
        ...mapGetters({
            units: 'getEstimateUnits',
        }),
    },
    methods: {
        load_resources(){
            this.$root.global_loading = true;
            this.$http.get('/api/resources?limit=9999').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.resources = res.data.rows;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        load_fichas(){
            this.$root.global_loading = true;
            this.$http.get('/api/fichas?limit=9999').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.fichas = res.data.rows;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        load_estimate_fork(){
            this.$root.global_loading = true;
            this.$http.get('/api/estimate_forks/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.currentEstimateFork = res.data;
                    this.isCreating = false;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        save: function(){
            this.$root.global_loading = true;
            if (this.isCreating) {
                this.$http.post('/api/estimate_forks/', this.currentEstimateFork).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t('estimate_fork.Fork_saved'), this.$root.$t("template.Success"));
                        this.$router.push({ name: 'estimate_forks_settings' });
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$root.global_loading = false;
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            } else {
                this.$http.patch('/api/estimate_forks/' + this.currentEstimateFork.id, this.currentEstimateFork).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t('estimate_fork.Fork_saved'), this.$root.$t("template.Success"));
                        this.$router.push({ name: 'estimate_forks_settings' });
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$root.global_loading = false;
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            }
        },
        add_entity: function(){
            let fork_entity = {
                object: 1,
                entity_rules: [],
                entity_changes: [],
                estimate_fork_id: null,
                order: this.currentEstimateFork.fork_entities.length == 0 ? 1 : this.currentEstimateFork.fork_entities.length + 1,
            };
            this.add_rule(fork_entity);
            this.currentEstimateFork.fork_entities.push(fork_entity);
        },
        add_rule: function(fork_entity){
            let entity_rule = {
                field: 1,
                rule_type: 1,
                subject: '',
                resources: [],
            };
            fork_entity.entity_rules.push(entity_rule);
        },
        add_change: function(fork_entity){
            let entity_change = {
                change_type: 0,
                quantity_type: 1,
                resources: [],
            };
            fork_entity.entity_changes.push(entity_change);
        },
        add_rule_resource: function(entity_rule){
            let condition = {
                field: 1,
                rule_type: 1,
                subject: '',
            };
            entity_rule.resources.push(condition);
        },
        add_change_resource: function(entity_change){
            let resource = {
                field: 1,
                resource_id: null,
                subject: '',
            };
            entity_change.resources.push(resource);
        },
        remove_entity_change_resource: function(resource, entity_change){
            if (confirm(this.$root.$t("estimate_fork.Are_you_sure_want_to_delete_this"))){
                let index = entity_change.resources.indexOf(resource);
                entity_change.resources.splice(index, 1);
            }
        },
        remove_entity_change: function(entity_change, fork_entity){
            if (confirm(this.$root.$t("estimate_fork.Are_you_sure_want_to_delete_this"))){
                let index = fork_entity.entity_changes.indexOf(entity_change);
                fork_entity.entity_changes.splice(index, 1);
            }
        },
        remove_entity_rule: function(entity_rule, fork_entity){
            if (confirm(this.$root.$t("estimate_fork.Are_you_sure_want_to_delete_this"))){
                let index = fork_entity.entity_rules.indexOf(entity_rule);
                fork_entity.entity_rules.splice(index, 1);
            }
        },
        remove_entity_rule_resource: function(condition, entity_rule){
            if (confirm(this.$root.$t("estimate_fork.Are_you_sure_want_to_delete_this"))){
                let index = entity_rule.resources.indexOf(condition);
                entity_rule.resources.splice(index, 1);
            }
        },
        remove_entity: function(fork_entity){
            if (confirm(this.$root.$t("estimate_fork.Are_you_sure_want_to_delete_this"))){
                let index = this.currentEstimateFork.fork_entities.indexOf(fork_entity);
                this.currentEstimateFork.fork_entities.splice(index, 1);
                for (let i = index; i < this.currentEstimateFork.fork_entities.length; i++){
                    this.currentEstimateFork.fork_entities[i].order = this.currentEstimateFork.fork_entities[i].order - 1;
                }
            }
        },
        to_up: function(fork_entity){
            if (fork_entity.order > 1){
                this.currentEstimateFork.fork_entities.splice(fork_entity.order - 1, 1);
                this.currentEstimateFork.fork_entities.splice(fork_entity.order - 2, 0, fork_entity);
                this.currentEstimateFork.fork_entities[fork_entity.order - 1].order++;
                fork_entity.order--;
            }
        },
        to_down: function(fork_entity){
            if (fork_entity.order < this.fork.fork_entities.length){
                this.currentEstimateFork.fork_entities.splice(fork_entity.order - 1, 1);
                this.currentEstimateFork.fork_entities.splice(fork_entity.order, 0, fork_entity);
                this.currentEstimateFork.fork_entities[fork_entity.order - 1].order--;
                fork_entity.order++;
            }
        },
    },
}
</script>