<template lang="pug">
    div.diga-container.mb-3
        div.d-flex.justify-content-between.flex-wrap
            div.header-first-third
            div.d-none.d-md-block.header-second {{ $t('dashboard.Widgets') }}
            div.header-first-third(v-if="w_use_range")
                label(for="dashboard-range", v-text="$t('dashboard.range')")
                date-picker#dashboard-range(v-model="w_range", :first-day-of-week="$root.global_settings.first_day_of_week", range, type="date", :lang="$root.locale == 'pt' ? 'pt-br' : $root.locale", style="width:100%;")
            div.use_range_container.header-first-third
                label(v-text="$t('dashboard.view_type')")
                bootstrap-toggle(v-model="w_use_range", :options="{ on: $t('dashboard.yes'), off: $t('dashboard.no') }", style="width: 100%")
</template>

<script>

import moment from 'moment';

export default {
    data() {
        return {

        }
    },
    computed: {
        w_range: {
            get() {
                return this.$store.getters['stage/w_range'];
            },
            set(value) {
                this.$store.dispatch('stage/updateWRange', [
                    moment(value[0]).toISOString(),
                    moment(value[1]).toISOString(),
                ]);
            },
        },
        w_use_range: {
            get() {
                return this.$store.getters['stage/w_use_range'];
            },
            set(value) {
                this.$store.dispatch('stage/updateWUseRange', value);
            },
        },
    },
}

</script>