<template lang="pug">
    div.diga-container
        div.d-flex.justify-content-between.flex-wrap
            div.header-first-third
                label(for="dashboard-responsible", v-text="$t('dashboard.responsible')")
                select#dashboard-responsible.form-control(v-model="responsible")
                    option(value="0") {{ $t('dashboard.all') }}
                    option(v-for="user in users.filter(u => u.active === true)", :value="user.id", v-text="user.name")
            div.d-none.d-md-block.header-second {{ $t('dashboard.Service_states_data') }}
            div.header-first-third(v-if="use_range")
                label(for="dashboard-range", v-text="$t('dashboard.created_range')")
                date-picker#dashboard-range(v-model="range", :first-day-of-week="$root.global_settings.first_day_of_week", range, type="date", :lang="$root.locale == 'pt' ? 'pt-br' : $root.locale", style="width:100%;")
            div.use_range_container.header-first-third
                label(v-text="$t('dashboard.view_type')")
                bootstrap-toggle(v-model="use_range", :options="{ on: $t('dashboard.yes'), off: $t('dashboard.no') }", style="width: 100%")
</template>

<script>

import moment from 'moment';
import {mapGetters} from "vuex";

export default {
    data() {
        return {
            responsible: this.$store.getters['stage/responsible'],
            range: this.$store.getters['stage/range'],
            use_range: this.$store.getters['stage/use_range'],
        }
    },
    watch: {
        range(val) {
            this.$store.dispatch('stage/range', [
                moment(val[0]).toISOString(),
                moment(val[1]).toISOString(),
            ]);
        },
        responsible(val) {
            this.$store.dispatch('stage/responsible', val);
        },
        use_range(val) {
            this.$store.dispatch('stage/set_use_range', val);
        },
    },
    computed: {
        ...mapGetters({
            users: 'getUsers',
        }),
    },
}

</script>