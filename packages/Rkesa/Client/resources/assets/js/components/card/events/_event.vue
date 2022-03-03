<style>
    .done-button {
        vertical-align: middle;
        height: 23px;
        width: 23px;
        background-color: #4c4c4c;
        padding: 0;
        color: #ffffff;
    }
    .event-label {
        overflow: hidden;
        height: 21px;
        max-width: 160px;
        padding: 0 5px;
        border: 1px solid #000000;
        border-radius: 3px;
        opacity: 0.5;
        text-align: center;
        font-size: 11pt;
        display: inline-block;
        vertical-align: middle;
    }
    .fa.fa-user {
        color: rgb(255, 255, 255);
        font-size: 16pt;
        padding-top: 4px;
    }
    .event-caption {
        vertical-align: middle;
    }
    .event-description{
        font-size: 11pt;
        padding-left: 30px;
        opacity: 0.5;
        line-height: 1.2;
        margin-top: 4px;
    }
</style>

<template lang="pug">
    li
        .item-1.with-gradient
            div.cont
                div.cont-col1
                    div.status-icon(v-bind:style="{'background-color': event_type.color}")
                        i(v-bind:class="[{'fa': true}, event_type.icon]")
                div.cont-col3
                    div.date(style="width: 20%;float: right;") {{ event.start }}
                div.cont-col2
                    div
                        span(:class="{'event-caption mr-2': true, 'clickable clickable-link': active && $root.can_with_event('update', event)}" v-on:click="edit_event(event)") {{ event_type.title }}
                        button.btn.done-button.mr-2(v-if="active && $root.can_with_event('addit', event)" v-on:click="make_done()")
                            i(v-bind:class="{'fa': true, 'fa-check-circle-o': !event.done, 'fa-check-circle': event.done}")
                        span.event-label(v-if="event.user_id") {{ users_by_id[event.user_id].name }}
                    div.event-description(v-if="event.description") {{ event.description }}
                div.clearfix
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    data(){
        return {
            myevent: this.event,
        }
    },
    props: ['event', 'active'],
    methods: {
        make_done: function(){
            if (!this.event.done) {
                this.$emit('done', this.event);
            }
        },
        edit_event: function(event){
            if (!event.done && this.$root.can_with_event('update', event)) {
                this.$emit('edit', event);
            }
        },
    },
    computed: {
        event_type: function(){
            return this.event_types_by_id[this.event.event_type_id];
        },
        ...mapGetters({
            event_types_by_id: 'getEventTypesById',
            users_by_id: 'getUsersById',
        }),
    },
}
</script>