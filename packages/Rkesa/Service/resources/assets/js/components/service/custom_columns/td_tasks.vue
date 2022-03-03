<style>
    .service-event{
        border-radius: 3px;
        padding: 5px;
        color: #ffffff;
        font-size: .85em;
        line-height: 1.3;
        margin-bottom: 5px;
    }
    .service-event .time{
        font-weight: bold;
        padding: 0;
    }
    .service-event span {
        padding: 3px;
    }
</style>

<template lang="pug">
    div
        div.service-event.clickable(v-for="event in row.active_events", :style="{'background-color': event_types_by_id[event.event_type_id].color}" v-on:click="open_event(event)")
            span.time {{ task_date_format(event.start) }}
            span.title
                span(:class="['fa', event_types_by_id[event.event_type_id].icon]")
                | {{ event_types_by_id[event.event_type_id].title }}
                span {{ usersById[event.user_id].name }}
</template>

<script>
import {mapGetters} from "vuex";
import moment from 'moment';

export default {
    props: ['row'],
    computed: {
        ...mapGetters({
            event_types_by_id: 'getEventTypesById',
            usersById: 'getUsersById',
        }),
    },
    methods: {
        task_date_format(datetime){
            return moment(datetime).format('HH:mm');
        },
        open_event(event){
            this.$bus.$emit('services_open_event', event);
        },
    },
}
</script>