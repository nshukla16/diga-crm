<template lang="pug">
button.btn.btn-danger(v-on:click="delete_resource(row.id)") {{ $t('template.Remove') }}
</template>

<script>
export default {
  props: ["row"],
  methods: {
    delete_resource(id) {
      if (confirm(this.$root.$t("calendar.AreYouSure"))) {
        this.$http.delete("/api/products/" + id).then(
          (data) => {
            if (data.errcode == 1) {
              this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("template.saved"),
                this.$root.$t("template.Success")
              );
              this.$bus.$emit("get_results");
            }
          },
          (res) => {
            this.$toastr.e(
              this.$root.$t("template.Server_error"),
              this.$root.$t("template.Error")
            );
          }
        );
      }
    },
  },
};
</script>