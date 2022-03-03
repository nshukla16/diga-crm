<template lang="pug">
span 
  template(v-if="$root.can_do('products', 'edit') != 0")
    .input-group
      input.form-control(
        v-model.number="row.quantity",
        type="number",
        step="0.1",
        @change="isEdit = true"
      )
      .input-group-append
        button.btn.btn-outline-secondary(@click="save", :disabled="!isEdit")
          i.fa.fa-check
    span
  template(v-else) {{ row.quantity }}
</template>

<script>
export default {
  props: ["row"],
  data: function () {
    return {
      isEdit: false,
    };
  },
  methods: {
    save() {
      this.$root.global_loading = true;
      this.$http.patch("/api/products/" + this.row.id, this.row).then(
        (res) => {
          if (res.data.errcode == 1) {
            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
          } else {
            this.$toastr.s(
              this.$root.$t("template.saved"),
              this.$root.$t("template.Success")
            );
            this.isEdit = false;
          }
          this.$root.global_loading = false;
        },
        (res) => {
          this.$toastr.e(
            this.$root.$t("template.Server_error"),
            this.$root.$t("template.Error")
          );
          this.$root.global_loading = false;
        }
      );
    },
  },
};
</script>