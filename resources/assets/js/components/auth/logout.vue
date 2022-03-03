<template lang="pug">
.container
</template>

<script>
export default {
  mounted() {
    this.logout();
  },
  data() {
    return {};
  },
  methods: {
    logout() {
      this.$store.dispatch("authLogout").then(() => {
        this.$root.leave_all_channels();
      });
    },
  },
};
</script>