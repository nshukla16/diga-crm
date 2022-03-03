<style>

</style>

<template lang="pug">
    div.col-12.col-md-6.mb-3
        div.diga-container.p-4
            h2 {{ $t("calendar_extended.Event_groups") }}
            div.table-responsive
                table.colors-table.w-100.mb-2
                    thead
                        tr
                            td(style="width: 40px;") №
                            td(style="min-width: 100px;") {{ $t("calendar_extended.Name") }}
                            td(style="width: 145px;") {{ $t("calendar_extended.Color") }}
                            td(style="width: 85px;")
                    tbody
                        tr(v-for="(event_group,i) in groups" style="margin-bottom: 5px;")
                            td
                                | {{ i+1 }}.
                            td
                                input.form-control(v-model="event_group.name")
                            td.picker-container
                                sketch-picker(v-if="selected[i]" v-model="event_group.color" v-on-clickaway="hide_picker")
                                div.color-icon.color(v-bind:style="{'background-color': event_group.color.hex}" v-on:click="show_picker(i)")
                                input.form-control.settings-inputs(style="width: 100px;" v-model="event_group.color.hex")
                            td
                                button.btn.red.ml-2(v-if="event_group.id != 1" v-on:click="remove(event_group)") {{ $t('template.Remove') }}
            button.btn(v-on:click="add()") {{ $t('template.Add') }}
</template>

<script>
import moment from 'moment';

export default {
    data() {
        return {
            groups: this.event_groups,
            selected: [],
            removed: [],
        }
    },
    props: ['event_groups'],
    mounted(){
        for (let i = 0; i < this.groups.length; i++) {
            this.selected.push(false);
            this.groups[i].color = {
                hex: this.groups[i].color,
            }
        }
        this.$bus.$on("calendar_settings_get_data", this.get_data);
    },
    beforeDestroy: function() {
        this.get_data && this.$bus.$off("calendar_settings_get_data", this.get_data);
    },
    methods: {
        get_data(){
            let payload = JSON.parse(JSON.stringify(this.$data));
            for (let i = 0; i < payload.groups.length; i++) {
                payload.groups[i].color = payload.groups[i].color.hex;
            }
            payload.selected = null;
            this.$bus.$emit("calendar_settings_data", payload);
        },
        add(){
            let event_group = {
                id: 0,
                name: '',
                color: {
                    hex: '#000000',
                },
            };
            this.groups.push(event_group);
        },
        remove(event_group){
            if (confirm(this.$root.$t("calendar_extended.Are_you_sure_want_to_delete_event_group"))){
                this.removed.push(event_group.id);
                let index = this.groups.indexOf(event_group);
                this.groups.splice(index, 1);
            }
        },
        show_picker(i){
            Vue.set(this.selected, i, true);
        },
        hide_picker(){
            for (let i = 0; i < this.groups.length; i++) {
                Vue.set(this.selected, i, false);
            }
        },
    },
}
</script>