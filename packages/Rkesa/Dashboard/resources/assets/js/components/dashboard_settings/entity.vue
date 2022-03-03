<style>
    .no-select {
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    .entity-inner{
        border:1px solid #ccc;
        padding: 0 20px;
        height: 100%;
    }
</style>

<template lang="pug">
    div.col-12.col-md-3.mb-3(v-if="type == 0")
        div.diga-container.p-4
            h2(v-text="$store.getters['dashboard/entity_status_name'](id)")
            .fields-box
                | {{ $t('dashboard.Columns') }}:
                .entity-field.input-group.form-group(v-for="field in entity_fields")
                    select.form-control(v-model="field.type", v-on:change="update_field(field.id, field.type)")
                        optgroup(:label="$t('client.Estimate')")
                            option(value="4") {{ $t('dashboard.master_sum') }}
                            option(value="5") {{ $t('dashboard.master_number') }}
                        optgroup(:label="$t('client.Service')")
                            option(value="1") {{ $t('dashboard.status_date') }}
                            option(value="8") {{ $t('dashboard.service_responsible') }}
                            option(value="9") {{ $t('dashboard.service_region') }}
                        optgroup(:label="$t('estimate.Client')")
                            option(value="6") {{ $t('dashboard.client_name') }}
                            option(value="7") {{ $t('dashboard.client_referer') }}
                        optgroup(:label="$t('service.Task')")
                            option(value="10") {{ $t('dashboard.responsible_first_task') }}
                            option(value="12") {{ $t('dashboard.first_task_date') }}
                            option(value="13") {{ $t('dashboard.first_selected_event_type_responsible') }}
                        optgroup(:label="$t('dashboard.Field_work')")
                            option(value="2" disabled) {{ $t('dashboard.service_ovp') }}
                            option(value="3" disabled) {{ $t('dashboard.service_data_inicio') }}
                            option(value="11" disabled) {{ $t('dashboard.work_final_date') }}
                    select.form-control(v-if="field.type == 12 || field.type == 13" v-model="field.event_type_id")
                        option(v-for="event_type in event_types", :value="event_type.id") {{ event_type.title }}
                    .input-group-btn(style="padding-left: 10px;")
                        button.btn(v-on:click="removeField(field.id)", v-text="$t('template.Remove')")
            .fields-navigation.form
                .input-group(style="margin-bottom:10px;")
                    .input-group-btn
                        button.btn.btn-diga.btn-sm(v-on:click="addField()")
                            span.fa.fa-plus
                    .mt-checkbox-inline
                        label.mt-checkbox(style="margin:0;margin-left:10px;").no-select
                            input.mr-2(type="checkbox", value="0", v-model="hide", v-on:change="toggleHide()")
                            | {{ $t('dashboard.hide')}}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['id'],
    data() {
        return {
            entity_fields: this.$store.getters['dashboard/entity_fields'](this.id),
            hide: this.$store.getters['dashboard/entity_hide'](this.id),
            type: this.$store.getters['dashboard/entity_type'](this.id),
        }
    },
    computed: {
        ...mapGetters({
            event_types: 'getEventTypes',
        }),
    },
    methods: {
        _updateModels() {
            this.entity_fields = this.$store.getters['dashboard/entity_fields'](this.id);
            this.hide = this.$store.getters['dashboard/entity_hide'](this.id);
        },
        addField() {
            this.$store.dispatch('dashboard/addEntityField', this.id);
            this._updateModels();
        },
        removeField(field_id) {
            this.$store.dispatch('dashboard/removeEntityField', { 'entity_id': this.id, 'field_id': field_id });
            this._updateModels();
        },
        toggleHide() {
            this.$store.dispatch('dashboard/toggleEntityHide', this.id);
            this._updateModels();
        },
        update_field(field_id, field_val){
            this.$store.dispatch('dashboard/updateEntityFields', {'entity_id': this.id, 'field_id': field_id, 'field_value': field_val});
            this._updateModels();
        },
    },
}

</script>