<template lang="pug">
div
  p(v-for="us in row.user_salaries") {{ $t('estimate.Start_time') }}: {{ dt(us.start) }} - {{ us.amount }}{{ $root.current_currency.symbol + ' ' }}
    template(v-if="us.salary_type === false") {{ $t('hr.Mensal') }}
    template(v-else) {{ $t('hr.Por_hora') }}
</template>

<script>
import moment from "moment";
export default {
  props: ["row"],
  methods: {
    dt(date) {
      return moment(date).format("YYYY-MM-DD");
    },
  },
};
</script>