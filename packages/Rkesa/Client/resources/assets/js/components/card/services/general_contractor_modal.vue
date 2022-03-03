<template lang="pug">
.modal.fade(
  :id="'modal-general-contractor_' + service.id",
  tabindex="-1",
  aria-hidden="true"
)
  .modal-dialog.modal-dialog-centered.modal-lg(role="document")
    .modal-content
      .modal-header {{ $t('template.general_contractor') }}
      .modal-body
        .row
          .col-6
            .row.my-2
              .col-2 {{ $t('estimate.state') }}
              .col-10(v-if="service.contractor_status") {{ $t('template.' + service.contractor_status) }}
              .col-10(v-else) {{ $t('hr.begin') }}
            .row.my-2(v-if="service.contractor_decline_reason != null")
              .col-2 {{ $t('template.reason_of_declining') }}
              .col-10 {{ service.contractor_decline_reason }}
            .row.my-2
              .col-2 {{ $t('estimate.Preco') }}
              .col-10(v-if="service.contractor_status") {{ parseFloat(service.estimate_summ).toFixed(2) }} {{ $root.current_currency.symbol }}
              .col-10(v-else) {{ $t('hr.begin') }}
          .col-6
            .row.my-2
              .col-4 {{ $t('estimate.Work_start_date') }}
              .col-8 
                date-picker(
                  :disabled="true",
                  format="YYYY-MM-DD",
                  v-model="service.work_start",
                  :lang="$root.locale"
                )
            .row.my-2
              .col-4 {{ $t('estimate.Work_finish_date') }}
              .col-8 
                date-picker(
                  :disabled="true",
                  format="YYYY-MM-DD",
                  v-model="service.work_end",
                  :lang="$root.locale"
                )
            .row.my-2
              .col-4 {{ $t('hr.contract_file') }}
              .col-8 
                a(target="_blank", :href="'http://' + service.contractor_file") {{ service.contractor_file_name }}

        h4.text-center {{ $t('estimate.Pagamento_de_prestacoes') }}
        table.table.table-striped
          thead
            tr
              th {{ $t('estimate.Description') }}
              th {{ $t('project.Date') }}
              th %
              th {{ $t('client.total') }}

              th {{ $t('project.Invoice') }}
              th {{ $t('estimate.Paid') }}
              th {{ $t('estimate.Paid_to') }}
          tbody
            tr(
              v-for="(pay_stage, index) in service.contractor_service_pay_stages"
            )
              td {{ pay_stage.text }}

              td {{ pay_stage.payment_date }}
              td {{ pay_stage.percent }}

              td {{ parseFloat(calculate_pay_stage_value(pay_stage.percent, service.estimate_summ)).toFixed(2) }} {{ $root.current_currency.symbol }}

              td 
                file-uploader(
                  :file_url="pay_stage.invoice_file",
                  :file_name="pay_stage.invoice_file_name",
                  :editable="service.contractor_status === 'contractor_confirmed' || service.contractor_status === 'contractor_finished'",
                  @remove="remove_invoice_file(pay_stage)",
                  @finished="(arr) => { [pay_stage.invoice_file, pay_stage.invoice_file_name] = arr; is_changed = true; }"
                ) 
              td 
                template(v-if="pay_stage.paid === true") {{ $t('client.yes') }}
                template(v-else) {{ $t('client.no') }}

              td {{ parseFloat(pay_stage.fact_paid || 0).toFixed(2) }} {{ $root.current_currency.symbol }}

        .text-center.mt-2(v-if="is_changed")
          button.btn.btn-diga(@click="save_pay_stages()") {{ $t('template.send_invoices') }}

        .text-center(
          style="margin-top: 10px",
          v-if="service.contractor_status === 'awaiting_contractor_confirmation'"
        )
          button.btn.btn-diga(@click="update_status('contractor_confirmed')") {{ $t('template.agree_with_conditions') }}
          button.btn.btn-danger.mx-2(
            @click="update_status('contractor_declined')"
          ) {{ $t('template.decline_conditions') }}

        .text-center(
          style="margin-top: 10px",
          v-if="service.contractor_status === 'contractor_confirmed' || service.contractor_status === 'decline_work_finished'"
        )
          button.btn.btn-diga(@click="update_status('contractor_finished')") {{ $t('template.send_work_finished') }}
</template>

<script>
export default {
  props: ["service"],
  data() {
    return {
      is_changed: false,
    };
  },
  methods: {
    save_pay_stages() {
      if (confirm(this.$root.$t("calendar.AreYouSure"))) {
        this.service.contractor_service_pay_stages.forEach((ps) => {
          if (ps.invoice_file !== null) {
            this.$http
              .patch("/api/contractor_service_pay_stages/" + ps.id, ps)
              .then(
                (res) => {
                  if (res.data.errcode == 1) {
                    this.$toastr.e(
                      res.data.errmess,
                      this.$root.$t("template.Error")
                    );
                  } else {
                    this.$toastr.s(
                      this.$root.$t("template.invoice_sent"),
                      this.$root.$t("template.Success")
                    );
                    this.is_changed = false;
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
      }
    },
    calculate_pay_stage_value(percent, val) {
      if (percent > 0 && val > 0) {
        return val * (percent / 100);
      } else {
        return 0;
      }
    },
    remove_invoice_file(pay_stage) {
      if (confirm(this.$root.$t("template.document_wont_be_deleted_if"))) {
        pay_stage.invoice_file = null;
        pay_stage.invoice_file_name = null;
      }
    },
    update_status(status) {
      if (confirm(this.$root.$t("calendar.AreYouSure"))) {
        this.$root.global_loading = true;
        this.$http
          .put("/api/services/" + this.service.id + "/change_status", {
            contractor_status: status,
          })
          .then(
            (res) => {
              if (res.data.errcode == 1) {
                this.$toastr.e(
                  res.data.errmess,
                  this.$root.$t("template.Error")
                );
              } else {
                this.$toastr.s(
                  this.$root.$t("template.Success"),
                  this.$root.$t("template.Success")
                );
                this.service.contractor_status = status;
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
      }
    },
  },
};
</script>

<style scoped>
</style>