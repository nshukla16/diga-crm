<template lang="pug">
#modal-add-series.modal.fade(tabindex="-1", aria-hidden="true")
  .modal-dialog.modal-dialog-centered(role="document")
    .modal-content
      .modal-header {{ $t('template.create_series') }}
      .modal-body
        input.form-control(
          v-validate="{'alpha_num': true, required: true, min: 1, max: 3}",
          type="text",
          v-model="new_serie_name",
          style="width: 100%",
          :class="{ 'is-invalid': errors.has('new_serie_name') }",
          name="new_serie_name"
        )
        div(style="text-align: center")
          button.btn.green.my-3(
            :disabled="new_serie_name === ''",
            v-on:click="create_serie()"
          ) OK
</template>

<script>
export default {
  props: ["document_type_id", "invoice_series"],
  data() {
    return {
      new_serie_name: "",
    };
  },
  methods: {
    create_serie() {
      this.$validator.validateAll().then((result) => {
        if (!result) {
          this.$toastr.w(
            this.$root.$t("template.Need_to_fill"),
            this.$root.$t("template.Warning")
          );
          return;
        } else {
          if (this.invoice_series.some((i) => i.name === this.new_serie_name)) {
            this.$toastr.w(
              this.$root.$t("template.series_already_exists"),
              this.$root.$t("template.Warning")
            );
            return;
          }
          this.$http
            .post("/api/invoice_series", {
              name: this.new_serie_name,
              document_type_id: this.document_type_id,
            })
            .then(
              (res) => {
                if (res.data.errcode == 1) {
                  this.$toastr.e(
                    res.data.errmess,
                    this.$root.$t("template.Error")
                  );
                } else {
                  this.$emit("get_invoice_series");
                  this.$emit("update_invoice_series_id", res.data.id);
                  jQuery("#modal-add-series").modal("hide");
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
      });
    },
  },
};
</script>

<style lang="scss" scoped>
</style>