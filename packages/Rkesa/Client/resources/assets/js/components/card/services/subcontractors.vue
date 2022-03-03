<style>
.modal-body {
  max-width: 100%;
  overflow-x: auto;
}
.scoll-tree {
  max-width: inherit;
  /* width:5000px; */
}
</style>

<template lang="pug">
div
  h2 {{ $t('template.contractors_and_teams') }}
  section.diga-container.p-4
    .form(v-if="service")
      .row
        .col-2 {{ $t('calendar.Name') }}
        .col-10 {{ service.name }}
      .row
        .col-2 {{ $t('calendar.Service') }}
        .col-10 # {{ service.estimate_number }}
      .row.mb-3
        .col-2 {{ $t('client.Estimate_summ') }}
        .col-10
          template(v-if="estimate") {{ parseFloat(estimate.price).toFixed(2) }} {{ $root.current_currency.symbol }}
          template(v-else) {{ $t('hr.Estimate_not_set') }}

    template(v-for="estimate_group in estimate_groups") 
      h3.text-center.my-4 
        | {{ estimate_group.group.name + ' ' }}
        template(v-if="estimate_group.group.type !== 2") ({{ $t('template.Department') }})
        template(v-else) ({{ $t('template.contractor') }})
        | {{ ' ' + parseFloat(estimate_group.percent).toFixed(2) }} %
      .row.my-2
        .col-2 {{ $t('estimate.state') }}
        .col-10(v-if="estimate_group.contractor_status") {{ $t('template.' + estimate_group.contractor_status) }}
        .col-10(v-else) {{ $t('hr.begin') }}
      .row.my-2
        .col-2 {{ $t('estimate.in_percent') }}
        .col-10
          bootstrap-toggle(
            v-model="estimate_group.in_percent",
            :options="{ on: $t('template.Yes'), off: $t('template.No') }",
            data-width="80",
            data-height="36",
            data-onstyle="default"
          )
      .row.my-2(v-if="estimate_group.in_percent !== true")
        .col-2 €
        .col-10
          input.form-control(
            :disabled="estimate_group.contractor_status != null && estimate_group.contractor_status !== 'contractor_declined'",
            type="number",
            step="0.1",
            v-model.number="estimate_group.sum",
            @input="sum_change($event, estimate_group)",
            style="width: unset !important"
          ) 
      .row.my-2(v-else)
        .col-2 {{ $t('estimate.Percent') }}
        .col-10
          input.form-control(
            :disabled="estimate_group.contractor_status != null && estimate_group.contractor_status !== 'contractor_declined'",
            type="number",
            step="0.1",
            v-model.number="estimate_group.percent",
            style="width: unset !important"
          )

      .row.my-2
        .col-2 {{ $t('estimate.Work_start_date') }}
        .col-10 
          date-picker(
            :disabled="estimate_group.contractor_status != null && estimate_group.contractor_status !== 'contractor_declined'",
            format="YYYY-MM-DD",
            v-model="estimate_group.work_start",
            :lang="$root.locale"
          )
      .row.my-2
        .col-2 {{ $t('estimate.Work_finish_date') }}
        .col-10 
          date-picker(
            :disabled="estimate_group.contractor_status != null && estimate_group.contractor_status !== 'contractor_declined'",
            format="YYYY-MM-DD",
            v-model="estimate_group.work_end",
            :lang="$root.locale"
          )
      .row.my-2
        .col-2 {{ $t('hr.contract_file') }}
        .col-10 
          file-uploader(
            :file_url="estimate_group.contractor_file",
            :file_name="estimate_group.contractor_file_name",
            :editable="estimate_group.contractor_status == null || estimate_group.contractor_status === 'contractor_declined'",
            @remove="remove_estimate_group_contract(estimate_group)",
            @finished="(arr) => { [estimate_group.contractor_file, estimate_group.contractor_file_name] = arr; }"
          ) 
      h3 {{ $t('client.Pay_info') }}
      table.table.table-striped
        thead
          tr
            th {{ $t('client.type_of_the_vat') }}
            th %
            th {{ $t('client.base_value') }}
            th {{ $t('client.vat') }}
            th {{ $t('client.total') }}
        tbody
          tr
            td {{ $t('estimate.Auto_liquidacao') }}
            td -
            td {{ parseFloat(subcontractor_price(estimate_group.percent)).toFixed(2) }}
            td 0
            td {{ parseFloat(subcontractor_price(estimate_group.percent)).toFixed(2) }}
          tr
            td
            td
            td 
              b {{ parseFloat(subcontractor_price(estimate_group.percent)).toFixed(2) }}
            td 
              b 0
            td 
              b {{ parseFloat(subcontractor_price(estimate_group.percent)).toFixed(2) }}
      h3 {{ $t('estimate.Pagamento_de_prestacoes') }}
      table.table.table-striped
        thead
          tr
            th {{ $t('estimate.Description') }}
            th {{ $t('project.Date') }}
            th %
            th {{ $t('client.total') }}

            th {{ $t('project.Invoice') }} №
            th {{ $t('project.Invoice') }}
            th {{ $t('estimate.Paid') }}
            th {{ $t('estimate.Paid_to') }}
        tbody
          tr(
            v-for="(pay_stage, index) in estimate_group.estimate_group_pay_stages"
          )
            td
              input.form-control(
                :disabled="estimate_group.contractor_status != null && estimate_group.contractor_status !== 'contractor_declined'",
                v-model="pay_stage.text"
              )
            td 
              date-picker(
                :disabled="estimate_group.contractor_status != null && estimate_group.contractor_status !== 'contractor_declined'",
                format="YYYY-MM-DD",
                v-model="pay_stage.payment_date",
                :lang="$root.locale"
              )
            td 
              input.form-control(
                :disabled="estimate_group.contractor_status != null && estimate_group.contractor_status !== 'contractor_declined'",
                type="number",
                step="0.1",
                v-model.number="pay_stage.percent"
              )
            td {{ parseFloat(calculate_pay_stage_value(pay_stage.percent, estimate_group.percent)).toFixed(2) }}

            td 
              input.form-control(
                type="text",
                v-model="pay_stage.invoice_number"
              )
            td 
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
                step="0.01"
              )
            td
              button.btn.btn-danger(
                :disabled="estimate_group.contractor_status != null && estimate_group.contractor_status !== 'contractor_declined'",
                @click="remove_pay_stage(index, pay_stage, estimate_group)"
              ) {{ $t('client.Delete') }}

      .text-center
        button.btn.btn-default(
          :disabled="estimate_group.contractor_status != null && estimate_group.contractor_status !== 'contractor_declined'",
          @click="add_pay_stage(estimate_group)"
        ) {{ $t('calendar.Add') }}

      template(v-if="is_declining === true")
        label {{ $t('template.reason_of_declining') }}
        textarea.form-control.mb-3(
          v-model="estimate_group.contractor_decline_reason"
        )
        .text-center
          button.btn.btn-diga(
            :disabled="estimate_group.contractor_decline_reason == null || estimate_group.contractor_decline_reason === ''",
            v-on:click="changeStatus(estimate_group, 'decline_work_finished')"
          ) {{ $t('template.Check') }}
          button.btn.btn-danger.mx-2(v-on:click="is_declining = false") {{ $t('template.Cancel') }}

      template(v-else)
        button.btn.btn-diga(v-on:click="save(estimate_group)") {{ $t('template.Save') }}

        template(v-if="estimate_group.group.client")
          template(v-if="estimate_group.group.client.connection_id > 0")
            button.btn.btn-diga.mx-2(
              v-if="estimate_group.contractor_status == null",
              v-on:click="changeStatus(estimate_group, 'awaiting_contractor_confirmation')"
            ) {{ $t('template.send_to_contractor_for_confirmation') }}

            button.btn.btn-diga.mx-2(
              v-if="estimate_group.contractor_status === 'contractor_declined'",
              v-on:click="changeStatus(estimate_group, 'awaiting_contractor_confirmation')"
            ) {{ $t('template.send_to_confirm_one_more') }}

            button.btn.btn-diga.mx-2(
              v-if="estimate_group.contractor_status === 'contractor_finished'",
              v-on:click="changeStatus(estimate_group, 'work_finished')"
            ) {{ $t('template.confirm_works') }}
            button.btn.btn-danger.mx-2(
              v-if="estimate_group.contractor_status === 'contractor_finished'",
              v-on:click="is_declining = true"
            ) {{ $t('template.decline_completion_of_works') }}
          template(v-else)
            span.mx-3 {{ $t('template.need_to_connect_erp_to_profile') }}
      template(v-else)
        span.mx-3 {{ $t('template.need_to_create_profile') }}

    .text-center(v-if="estimate === null || estimate_groups.length === 0")
      h3 {{ $t('template.for_subcontracts') }}
      button.btn.btn-diga.mt-2(
        v-if="$root.can_do('estimates', 'create') != 0",
        v-on:click="open_create_estimate_page"
      ) {{ $t('client.Create_estimate') }}

    .text-center(style="margin-top: 10px")
      button.btn.btn-diga.mx-3(@click="showContractorsModal") {{ $t('estimate.add_subcontractor') }}
      router-link.btn.btn-diga(
        v-if="service && service.client_contact_id",
        :to="{ name: this.$root.contact_or_client_show_route(), params: { id: service.client_contact_id } }"
      ) {{ $t('estimate.Open_client_card') }}
  add_service_to_subcontractor(
    :service="service",
    :groupsToExclude="estimate_groups.map((eg) => { return eg.group_id; })",
    v-if="service",
    @fetch_services_from_child="getService"
  )
</template>

<script>
import moment from "moment";
import { mapGetters } from "vuex";
import add_service_to_subcontractor from "./add_service_to_subcontractor.vue";

export default {
  components: {
    add_service_to_subcontractor,
  },
  data() {
    return {
      service: null,
      is_declining: false,
    };
  },
  props: ["id"],
  mounted() {
    this.getService();
  },
  methods: {
    sum_change(event, estimate_group) {
      if (event.target.value > 0) {
        let sum = this.estimate ? this.estimate.price : 0.0;
        estimate_group.percent =
          sum === 0.0 ? 0.0 : (event.target.value / sum) * 100;
      }
    },
    showContractorsModal() {
      jQuery("#modal-contractors_" + this.service.id).modal("show");
    },
    changeStatus(estimate_group, status) {
      this.$root.global_loading = true;
      this.$http
        .put("/api/estimate_groups/change_status/" + estimate_group.id, {
          contractor_status: status,
          price: this.subcontractor_price(estimate_group.percent),
          contractor_decline_reason: estimate_group.contractor_decline_reason,
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
              this.is_declining = false;
              this.getService();
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
    add_pay_stage(estimate_group) {
      if (
        estimate_group.estimate_group_pay_stages &&
        estimate_group.estimate_group_pay_stages.length
      ) {
        estimate_group.estimate_group_pay_stages.push({
          id: 0,
          text: "",
          percent: 0,
          payment_date: moment().format("YYYY-MM-DD"),
          estimate_group_id: estimate_group.id,
          fact_paid: 0,
          invoice_file: null,
          invoice_file_name: null,
          //   invoice_number: null,
          paid: false,
        });
      }
    },
    remove_pay_stage(index, pay_stage, estimate_group) {
      if (confirm(this.$root.$t("calendar.AreYouSure"))) {
        if (pay_stage.id > 0) {
          if (!estimate_group.removed_estimate_group_pay_stages) {
            estimate_group.removed_estimate_group_pay_stages = [];
          }
          estimate_group.removed_estimate_group_pay_stages.push(pay_stage.id);
        }
        estimate_group.estimate_group_pay_stages.splice(index, 1);
      }
    },
    getService() {
      this.$root.global_loading = true;
      this.$http.get("/api/services/" + this.id).then(
        (res) => {
          this.service = res.data;
          this.$root.global_loading = false;
        },
        (res) => {
          this.$root.global_loading = false;
          this.$toastr.e(
            this.$root.$t("template.Server_error"),
            this.$root.$t("template.Error")
          );
        }
      );
    },
    save(estimate_group) {
      if (!estimate_group.service_id) {
        estimate_group.service_id = this.id;
      }
      this.$root.global_loading = true;
      this.$http
        .put("/api/estimate_groups/" + estimate_group.id, estimate_group)
        .then(
          (res) => {
            if (res.data.errcode == 1) {
              this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
            } else {
              this.$toastr.s(
                this.$root.$t("template.Success"),
                this.$root.$t("template.Success")
              );
              this.getService();
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

    calculate_pay_stage_value(percent, percent2) {
      if (percent > 0 && percent2 > 0) {
        return this.estimate.price * (percent / 100) * (percent2 / 100);
      } else {
        return 0;
      }
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
    remove_estimate_group_contract(estimate_group) {
      if (
        confirm(
          this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document")
        )
      ) {
        estimate_group.contractor_file = null;
        estimate_group.contractor_file_name = null;
      }
    },
    subcontractor_price(percent) {
      if (this.estimate) {
        return this.estimate.price * (percent / 100);
      } else {
        return 0;
      }
    },
    open_create_estimate_page() {
      this.$router.push({
        name: "estimate_create",
        query: { service_id: this.service.id },
      });
    },
  },
  computed: {
    estimate() {
      if (this.service) {
        if (this.service.master_estimate_id > 0) {
          let es = this.service.estimates.find(
            (e) => e.id === this.service.master_estimate_id
          );
          return es;
        } else {
          return null;
        }
      } else {
        return null;
      }
    },
    estimate_groups() {
      if (this.service) {
        let egs = this.service.groups;

        if (this.estimate && this.estimate.groups.length) {
          this.estimate.groups.forEach((group) => {
            if (!egs.some((e) => e.id === group.id)) {
              if (
                group.estimate_group_pay_stages &&
                group.estimate_group_pay_stages.length
              ) {
                group.estimate_group_pay_stages.forEach((eg) => {
                  if (!eg.text && eg.pay_stage) {
                    eg.text = eg.pay_stage.text;
                  }
                  if (!eg.percent && eg.pay_stage) {
                    eg.percent = eg.pay_stage.percent;
                  }
                  if (!eg.payment_date && eg.pay_stage) {
                    eg.payment_date = eg.pay_stage.payment_date;
                  }
                });
              } else {
                if (this.estimate && this.estimate.estimate_pay_stages.length) {
                  group.estimate_group_pay_stages = [];
                  this.estimate.estimate_pay_stages.forEach((ep) => {
                    group.estimate_group_pay_stages.push({
                      text: ep.text,
                      percent: ep.percent,
                      payment_date: ep.payment_date,
                      estimate_group_id: group.id,
                      fact_paid: 0,
                      invoice_file: null,
                      invoice_file_name: null,
                      invoice_number: null,
                      paid: false,
                    });
                  });
                }
              }

              egs.push(group);
            }
          });
        }

        egs.forEach((group) => {
          if (
            group.estimate_group_pay_stages.length === 0 &&
            this.estimate &&
            this.estimate.estimate_pay_stages.length
          ) {
            group.estimate_group_pay_stages = [];
            this.estimate.estimate_pay_stages.forEach((ep) => {
              group.estimate_group_pay_stages.push({
                text: ep.text,
                percent: ep.percent,
                payment_date: ep.payment_date,
                estimate_group_id: group.id,
                fact_paid: 0,
                invoice_file: null,
                invoice_file_name: null,
                invoice_number: null,
                paid: false,
              });
            });
          }

          group.sum =
            group.percent > 0
              ? parseFloat(this.subcontractor_price(group.percent)).toFixed(2)
              : 0;
        });

        return egs;
      } else {
        return [];
      }
    },
  },
};
</script>