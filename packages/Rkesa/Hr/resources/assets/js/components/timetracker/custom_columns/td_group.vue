<template lang="pug">
span {{ groupsById[row.group_id].name }}
</template>

<script>
import { mapGetters } from "vuex";

export default {
  props: ["row"],
  computed: {
    ...mapGetters({
      groupsById: "getGroupsById",
    }),
  },
};
</script>