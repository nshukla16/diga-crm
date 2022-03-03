<template lang="pug">
.container
  .row
</template>

<script>
export default {
  props: ["code"],
  mounted() {
    this.getCode();
  },
  computed: {},
  methods: {
    getCode: function () {
      const { code } = this;
      this.$store.dispatch("tokenRequest", { code }).then((bearer) => {
        initialize_bearer(bearer);
        if (!Push.Permission.has()) Push.Permission.request();
        // if (localStorage.getItem("returnUrl") === null) {
        this.$router.push("/dashboard");
        // } else {
        //   this.$router.push(localStorage.getItem("returnUrl"));
        // }
      });
    },
  },
};
</script>