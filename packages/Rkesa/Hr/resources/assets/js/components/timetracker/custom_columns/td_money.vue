<template lang="pug">
div {{ parseFloat(total_money).toFixed(2) }}{{ $root.current_currency.symbol }}
</template>

<script>
import moment from "moment";
export default {
  props: ["row"],
  methods: {
    hours_count(gw) {
      let result = 0;
      if (
        gw.date_end_before_lunch !== null &&
        gw.date_start_before_lunch !== null
      ) {
        result += moment
          .duration(
            moment(gw.date_end_before_lunch).diff(
              moment(gw.date_start_before_lunch)
            )
          )
          .asHours();
      }
      if (
        gw.date_end_after_lunch !== null &&
        gw.date_start_after_lunch !== null
      ) {
        result += moment
          .duration(
            moment(gw.date_end_after_lunch).diff(
              moment(gw.date_start_after_lunch)
            )
          )
          .asHours();
      }

      if (result < 0) {
        return 0;
      }
      let salary = this.row.user_salaries.find((us) => {
        return (
          moment(gw.date_start_before_lunch).isSameOrAfter(moment(us.start)) &&
          (us.end === null ||
            moment(gw.date_start_before_lunch).isSameOrBefore(moment(us.end)))
        );
      });
      return result * salary.amount;
    },
  },
  computed: {
    total_money() {
      var result = 0;
      this.row.estimate_group_workers.forEach((gw) => {
        result += this.hours_count(gw);
      });

      return result;
    },
  },
};
</script>