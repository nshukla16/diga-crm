<style scoped>
.modal-body {
  max-width: 100%;
  overflow-x: auto;
  height: 80vh;
  overflow-y: auto;
}
.scoll-tree {
  max-width: inherit;
  /* width:5000px; */
}
/* Important part */
.modal-dialog {
  overflow-y: initial !important;
}
</style>

<template lang="pug">
div
  .modal.fade(
    :id="'modal-finance_' + this.service.id",
    tabindex="-1",
    aria-hidden="true"
  )
    .modal-dialog.modal-lg(role="document", style="max-width: 90%")
      .modal-content
        .modal-header
          h5.modal-title {{ $t('client.Finances') }}
          button.close(v-on:click="close_modal", type="button")
            span(aria-hidden="true") &times;
        .modal-body
          .scoll-tree
            .form
              .form-group.row
                label.col-sm-2.col-form-label {{ $t('client.Estimate') }}
                .col-sm-10
                  input.form-control(
                    disabled="disabled",
                    :value="service.estimate_number"
                  )
              .form-group.row
                label.col-sm-4.col-form-label {{ $t('client.value_with_vat') }}
                .col-sm-4
                  input.form-control(
                    disabled="disabled",
                    :value="estimate.price"
                  )
                .col-sm-4
                  input.form-control(
                    v-if="estimate.vat_type === 1",
                    disabled="disabled",
                    :value="parseFloat(vat_value).toFixed(2)"
                  )
                  input.form-control(v-else, disabled="disabled", :value="0")
            h3(v-if="estimate.id") {{ $t('client.applicable_vat_rate') }}
            table.table.table-striped(v-if="estimate.id")
              thead
                tr
                  th {{ $t('client.type_of_the_vat') }}
                  th %
                  th {{ $t('client.base_value') }}
                  th {{ $t('client.vat') }}
                  th {{ $t('client.total') }}
              tbody
                tr(v-if="estimate.vat_type === 1")
                  td {{ $t('estimate.Labor') }}
                  td (6%)
                  td {{ parseFloat(labor_vat_value).toFixed(2) }}
                  td {{ parseFloat(labor_vat_value_vat).toFixed(2) }}
                  td {{ parseFloat(labor_vat_value + labor_vat_value_vat).toFixed(2) }}
                tr(v-if="estimate.vat_type === 1")
                  td {{ $t('estimate.Material') }}
                  td (23%)
                  td {{ parseFloat(material_vat_value).toFixed(2) }}
                  td {{ parseFloat(material_vat_value_vat).toFixed(2) }}
                  td {{ parseFloat(material_vat_value + material_vat_value_vat).toFixed(2) }}

                tr(v-if="estimate.vat_type === 2 || estimate.vat_custom > 0")
                  td {{ $t('estimate.Auto_liquidacao') }}
                  td -
                  td {{ parseFloat(auto_liquidacao_vat).toFixed(2) }}
                  td 0
                  td {{ parseFloat(auto_liquidacao_vat).toFixed(2) }}
                tr(v-if="estimate.vat_type === 3")
                  td {{ $t('estimate.Empresa') }}
                  td (23%)
                  td {{ estimate.price }}
                  td {{ parseFloat(empresa_vat).toFixed(2) }}
                  td {{ parseFloat(estimate.price + empresa_vat).toFixed(2) }}
                tr(v-if="estimate.vat_type === 5")
                  td {{ $t('estimate.Intra_community') }}
                  td -
                  td {{ estimate.price }}
                  td 0
                  td {{ estimate.price }}
                tr
                  td
                  td
                  td 
                    b {{ estimate.price }}
                  td 
                    b(
                      v-if="estimate.vat_type === 5 || estimate.vat_type === 2"
                    ) 0
                    b(v-if="estimate.vat_type === 3") {{ parseFloat(empresa_vat).toFixed(2) }}
                    b(
                      v-if="estimate.vat_type !== 5 && estimate.vat_type !== 2 && estimate.vat_type !== 3"
                    ) {{ parseFloat(material_vat_value_vat + labor_vat_value_vat).toFixed(2) }}
                  td 
                    b(
                      v-if="estimate.vat_type === 5 || estimate.vat_type === 2"
                    ) {{ parseFloat(estimate.price).toFixed(2) }}
                    b(v-if="estimate.vat_type === 3") {{ parseFloat(estimate.price + empresa_vat).toFixed(2) }}
                    b(
                      v-if="estimate.vat_type !== 5 && estimate.vat_type !== 2 && estimate.vat_type !== 3"
                    ) {{ parseFloat(material_vat_value_vat + labor_vat_value_vat + estimate.price).toFixed(2) }}
            h3(v-if="estimate.id") {{ $t('estimate.Pagamento_de_prestacoes') }}
            table.table.table-striped(v-if="estimate.id")
              thead
                tr
                  th {{ $t('estimate.Description') }}
                  th {{ $t('project.Date') }}
                  th %
                  th {{ $t('client.base_value') }}
                  th {{ $t('client.type_of_the_vat') }}
                  th {{ $t('client.vat') }}
                  th {{ $t('client.total') }}

                  th(v-if="$root.module_enabled('invoices')")
                  th {{ $t('project.Invoice') }} №
                  th {{ $t('project.Invoice') }}
                  th {{ $t('estimate.Paid') }}
                  th {{ $t('estimate.Paid_from') }}
                  th {{ $t('estimate.Recibo') }}
                  th {{ $t('estimate.Proof') }}
              tbody
                tr(v-for="(pay_stage, index) in estimate.estimate_pay_stages")
                  td {{ pay_stage.text }}
                  td {{ pay_stage.payment_date }}
                  td {{ pay_stage.percent }}
                  td {{ parseFloat(calculate_pay_stage_value(pay_stage.percent)).toFixed(2) }}
                  td
                    template(v-if="pay_stage.vat_type !== null")
                      template(v-if="pay_stage.vat_type === 1") 0 %
                      template(v-if="pay_stage.vat_type === 2") 6 %
                      template(v-if="pay_stage.vat_type === 3") 23 %
                      template(v-if="pay_stage.vat_type === 4") 6 & 23 %
                    template(v-else)
                      template(
                        v-if="estimate.vat_type === 5 || estimate.vat_type === 2"
                      ) 0%
                      template(v-if="estimate.vat_type === 3") 23%
                      template(v-if="estimate.vat_type === 1 && index === 0") 6 %
                      template(v-if="estimate.vat_type === 1 && index === 1") 23 %
                      template(v-if="estimate.vat_type === 1 && index === 2") 6 %
                      template(v-if="estimate.vat_type === 1 && index === 3") 6 %
                  td {{ parseFloat(calculate_pay_stage_vat(pay_stage.percent, pay_stage.vat_type, index)).toFixed(2) }}
                  td {{ parseFloat(calculate_pay_stage_value(pay_stage.percent) + calculate_pay_stage_vat(pay_stage.percent, pay_stage.vat_type, index)).toFixed(2) }}
                  td(v-if="$root.module_enabled('invoices')")
                    button.btn.btn-diga(
                      v-on:click="send_invoice(pay_stage, index)"
                    ) {{ $t('estimate.Send_invoice') }}
                  td 
                    input.form-control(
                      type="text",
                      v-model="pay_stage.invoice_number",
                      style="width: 100px"
                    )
                  td
                    template(
                      v-if="$root.module_enabled('invoices') && pay_stage.invoices && pay_stage.invoices.length > 0"
                    )
                      a(
                        v-for="invoice in pay_stage.invoices",
                        :href="'/invoices/pdf/' + invoice.id",
                        target="_blank"
                      ) {{ invoice.invoice_no }}
                    template(v-else)
                      file-uploader(
                        :file_url="pay_stage.invoice_file",
                        :file_name="pay_stage.invoice_file_name",
                        :editable="true",
                        @remove="remove_invoice_file(pay_stage)",
                        @finished="(arr) => { [pay_stage.invoice_file, pay_stage.invoice_file_name] = arr; }"
                      ) 
                  td 
                    bootstrap-toggle(
                      v-model="pay_stage.paid",
                      :options="{ on: $t('template.Yes'), off: $t('template.No') }",
                      data-width="80",
                      data-height="36",
                      data-onstyle="default"
                    )
                  td
                    input.form-control(
                      type="number",
                      v-model="pay_stage.fact_paid",
                      step="0.01",
                      style="width: 80px"
                    )
                  td 
                    file-uploader(
                      :file_url="pay_stage.recibo_file",
                      :file_name="pay_stage.recibo_file_name",
                      :editable="true",
                      @remove="remove_recibo_file(pay_stage)",
                      @finished="(arr) => { [pay_stage.recibo_file, pay_stage.recibo_file_name] = arr; }"
                    ) 
                  td 
                    file-uploader(
                      :file_url="pay_stage.proof_file",
                      :file_name="pay_stage.proof_file_name",
                      :editable="true",
                      @remove="remove_proof_file(pay_stage)",
                      @finished="(arr) => { [pay_stage.proof_file, pay_stage.proof_file_name] = arr; }"
                    ) 
            .float-right(style="padding-right: 100px")
              b(v-if="estimate.estimate_pay_stages") {{ $t('estimate.Paid_from') }} {{ $t('client.total') }}: {{ sum_fact_paid_total() }} {{ $root.current_currency.symbol }}

            button.btn.btn-diga(v-if="estimate.id", v-on:click="save") {{ $t('template.Save') }}

            .text-center(
              v-if="!estimate.id || (estimate && estimate.estimate_pay_stages && estimate.estimate_pay_stages.length === 0)"
            )
              h3 {{ $t('template.for_finances') }}
              button.btn.btn-diga.mt-2(
                v-if="$root.module_enabled('estimate') && $root.can_do('estimates', 'create') != 0",
                v-on:click="open_create_estimate_page"
              ) {{ $t('client.Create_estimate') }}
</template>

<script>
import moment from "moment";
import { mapGetters } from "vuex";

export default {
  data() {
    return {
      estimate: {
        price: 0,
      },
    };
  },
  props: ["service"],
  created() {
    // var totalwidth = 190 * $('.list-group').length;
    // $('.scoll-tree').css('width', totalwidth);
  },
  mounted() {
    // if (this.service.master_estimate_id !== null){
    //     this.load_estimate(this.service.master_estimate_id);
    // }
  },
  methods: {
    send_invoice(pay_stage, index) {
      jQuery("#modal-finance_" + this.service.id).modal("hide");
      this.$router.push({
        name: "invoice_create_pay_stage",
        params: { id: pay_stage.id },
      });
    },
    sum_fact_paid_total() {
      var total = 0.0;
      this.estimate.estimate_pay_stages.forEach((p) => {
        if (p.fact_paid !== null) {
          total += parseFloat(p.fact_paid);
        }
      });

      return total;
    },
    load_estimate(master_estimate_id) {
      if (master_estimate_id !== null) {
        this.$root.global_loading = true;
        this.$http.get("/api/estimates/" + master_estimate_id).then(
          (res) => {
            this.estimate = res.data;
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
    save() {
      this.$root.global_loading = true;
      this.$http
        .post("/api/estimate_pay_stages", {
          estimate_pay_stages: this.estimate.estimate_pay_stages,
          service_id: this.service.id,
          client_contact_id: this.service.client_contact.id,
        })
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("template.Success"),
                this.$root.$t("template.Success")
              );
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
    calculate_pay_stage_value(percent) {
      return this.estimate.price * (percent / 100);
    },
    calculate_pay_stage_vat(percent, vat_type, index) {
      if (this.estimate.vat_type === 5 || this.estimate.vat_type === 2) {
        return 0;
      }

      var value = this.calculate_pay_stage_value(percent);

      if (this.estimate.vat_type === 3) {
        return value * 0.23;
      }

      if (this.estimate.vat_type === 1) {
        switch (index) {
          case 0:
            return value * 0.06;
          case 1:
            return value * 0.23;
          case 2:
            return value * 0.06;
          case 3:
            return value * 0.06;
        }
      }

      switch (vat_type) {
        case 1:
          return value;
        case 2:
          return value * 0.06;
        case 3:
          return value * 0.23;
        case 4:
          var labor_v = value * (this.estimate.vat_maodeobra / 100) * 0.06;
          var material_v = value * (this.estimate.vat_material / 100) * 0.23;
          return labor_v + material_v;
      }

      return 0;
    },
    remove_invoice_file(pay_stage) {
      if (
        confirm(
          this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document")
        )
      ) {
        pay_stage.invoice_file = null;
        pay_stage.invoice_file_name = null;
      }
    },
    remove_recibo_file(pay_stage) {
      if (
        confirm(
          this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document")
        )
      ) {
        pay_stage.recibo_file = null;
        pay_stage.recibo_file_name = null;
      }
    },
    remove_proof_file(pay_stage) {
      if (
        confirm(
          this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document")
        )
      ) {
        pay_stage.proof_file = null;
        pay_stage.proof_file_name = null;
      }
    },
    open_create_estimate_page() {
      jQuery("#modal-finance_" + this.service.id).modal("hide");
      this.$router.push({
        name: "estimate_create",
        query: { service_id: this.service.id },
      });
    },
    close_modal() {
      if (confirm(this.$root.$t("calendar.AreYouSure"))) {
        jQuery("#modal-finance_" + this.service.id).modal("hide");
      }
    },
  },
  computed: {
    vat_value() {
      return this.labor_vat_value_vat + this.material_vat_value_vat;
    },
    labor_vat_value() {
      if (this.estimate) {
        return this.estimate.price * (this.estimate.vat_maodeobra / 100);
      } else {
        return 0;
      }
    },
    labor_vat_value_vat() {
      if (this.estimate) {
        return this.estimate.price * (this.estimate.vat_maodeobra / 100) * 0.06;
      } else {
        return 0;
      }
    },
    material_vat_value() {
      if (this.estimate) {
        return this.estimate.price * (this.estimate.vat_material / 100);
      } else {
        return 0;
      }
    },
    material_vat_value_vat() {
      if (this.estimate) {
        return this.estimate.price * (this.estimate.vat_material / 100) * 0.23;
      } else {
        return 0;
      }
    },
    auto_liquidacao_vat() {
      if (this.estimate) {
        if (this.estimate.vat_custom) {
          return this.estimate.price * (this.estimate.vat_custom / 100);
        } else {
          return this.estimate.price;
        }
      } else {
        return 0;
      }
    },
    empresa_vat() {
      if (this.estimate) {
        return this.estimate.price * (23 / 100);
      } else {
        return 0;
      }
    },
  },
};
</script>