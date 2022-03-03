<style>

</style>

<template lang="pug">
    v-select.mx-2.mb-2(multiple style="flex: 2 1 0%; min-width: 150px;", v-bind:value="value", v-on:input="$emit('groups_changed', $event)", :debounce='250', :options='groups', :placeholder="$t('calendar_extended.All_event_groups')")
        template(slot="no-options") {{ $t('template.No_matching_options') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    data() {
        return {

        }
    },
    props: ['value'],
    methods: {

    },
    computed: {
        ...mapGetters({
            event_groups: 'getEventGroups',
        }),
        groups(){
            return this.event_groups.map(function(currentValue, index, array){
                return {
                    label: currentValue.name,
                    value: currentValue.id,
                }
            });
        },
    },
}
</script>