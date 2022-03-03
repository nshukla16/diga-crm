<template lang="pug">
span {{ dt(row.from) }} - {{ dt(row.to) }}
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