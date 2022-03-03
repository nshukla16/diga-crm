<template lang="pug">
    div
        div.row
            fieldset.form-group.col-6
                label(for="d-w-color1", v-text="$t('dashboard.1week2week')")
                .input-group
                    input.form-control(id="d-w-color1", type="text", v-model="color1", placeholder="#color")
            fieldset.form-group.col-6
                label(for="d-w-color2", v-text="$t('dashboard.2week3week')")
                .input-group
                    input.form-control(id="d-w-color2", type="text", v-model="color2", placeholder="#color")
        div.row
            fieldset.form-group.col-6
                label(for="d-w-color3", v-text="$t('dashboard.3week4week')")
                .input-group
                    input.form-control(id="d-w-color3", type="text", v-model="color3", placeholder="#color")
            fieldset.form-group.col-6
                label(for="d-w-color4", v-text="$t('dashboard.4weekAndMore')")
                .input-group
                    input.form-control(id="d-w-color4", type="text", v-model="color4", placeholder="#color")
        div
            label(for="show-responsible-of-task", v-text="$t('dashboard.show_responsible_of_task')")
            select#show-responsible-of-task.form-control(v-model="event_type_id")
                option(v-for="event_type in event_types", v-text="event_type.title", :value="event_type.id")
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['id'],
    data(){
        return {

        }
    },
    computed: {
        ...mapGetters({
            event_types: 'getEventTypes',
        }),
        widget(){
            return this.$store.getters['dashboard/getWidgetsById'][this.id];
        },
        color1: {
            get() {
                return this.widget ? this.widget.color1 : '#33af0e';
            },
            set(value) {
                this.$store.dispatch('dashboard/updateWidgetColor1', { id: this.id, value: value });
            },
        },
        color2: {
            get() {
                return this.widget ? this.widget.color2 : '#2f7bf5';
            },
            set(value) {
                this.$store.dispatch('dashboard/updateWidgetColor2', { id: this.id, value: value });
            },
        },
        color3: {
            get() {
                return this.widget ? this.widget.color3 : '#480c86';
            },
            set(value) {
                this.$store.dispatch('dashboard/updateWidgetColor3', { id: this.id, value: value });
            },
        },
        color4: {
            get() {
                return this.widget ? this.widget.color4 : '#232225';
            },
            set(value) {
                this.$store.dispatch('dashboard/updateWidgetColor4', { id: this.id, value: value });
            },
        },
        event_type_id: {
            get() {
                return this.widget ? this.widget.event_type_id : this.event_types[0].id;
            },
            set(value) {
                this.$store.dispatch('dashboard/updateWidgetEvent', { id: this.id, value: value });
            },
        },
    },
}
</script>